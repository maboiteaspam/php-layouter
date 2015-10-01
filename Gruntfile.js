var exec = require('child_process').exec;
var chokidar = require('chokidar');
var async = require('async');

module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    dumps_file_path: [],

    open : {
      browser : {
        path: 'http://127.0.0.1:8000/'
      },
      options: {
        delay: 3000
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-open');

  var childs = [];
  process.on('SIGINT', function() {
    childs.forEach(function (destroyChild) {
      destroyChild(true);
    })
  });
  process.on('exit', function() {
    childs.forEach(function (destroyChild) {
      destroyChild();
    })
  });
  var spawnPhp = function(cmd, done, voidStdout){
    var killed = false;
    var stdout = '';
    var stderr = '';
    grunt.log.subhead(cmd)
    var child = exec(cmd, { stdio: 'pipe' },
      function (error) {
        if (!killed && error !== null) {
          grunt.log.error('php server exec error: ' + error);
        }
        done(error, stdout, stderr);
      });
    child.stdout.on('data', function (d){
      if (!voidStdout) {
        grunt.log.success((''+d).replace(/\s+$/, ''))
      }
      stdout += d;
    });
    child.stderr.on('data', function (d){
      grunt.log.error((''+d).replace(/\s+$/, ''))
      stderr += d;
    });
    childs.push(function (wasKilled) {
      killed = wasKilled;
      child.kill();
    })
    return child;
  };
  var spawnWatchr = function (watchPaths) {
    grunt.log.ok('Watching paths');
    grunt.log.writeflags(watchPaths)
    // Full list of options. See below for descriptions.
    chokidar.watch(watchPaths, {
      persistent: true,

      ignoreInitial: true,
      followSymlinks: true,
      cwd: '.',

      //usePolling: false,
      alwaysStat: false,
      depth: 3,
      interval: 250,

      ignorePermissionErrors: false,
      atomic: true
    }).on('all', function(event, filePath){
      spawnPhp('php cli.php cache:update '+event+' '+filePath, function (error) {
        grunt.log.ok('cache is now up to date');
      });
    })
  };

  grunt.registerTask('db-init', function() {
    var done = this.async();
    spawnPhp('php cli.php db:init', function (error) {
      done(error);
    });
  });
  grunt.registerTask('cache-init', function() {
    var done = this.async();
    spawnPhp('php cli.php cache:init', function (error) {
      done(error);
    });
  });
  grunt.registerTask('http-init', function() {
    var done = this.async();
    spawnPhp('php cli.php http:bridge', function (error) {
      done(error);
    });
  });
  grunt.registerTask('check-schema', function() {
    var done = this.async();
    spawnPhp('php cli.php db:refresh', function (error) {
      done(error);
    });
  });
  grunt.registerTask('fs-cache-dump', function() {
    var done = this.async();
    var path_to_watch = [];

    spawnPhp('php cli.php fs-cache:dump', function (error, stdout, stderr) {
      var data = JSON.parse(stdout);
      data.forEach(function(cache){
        if (cache.config
          && cache.config.paths
          && cache.config.paths.length) {
          grunt.log.success('paths %j', cache.config.paths);
          path_to_watch = path_to_watch.concat(cache.config.paths)
        } else {
          grunt.log.success('items count %s', cache.items.length);
          Object.keys(cache.items).forEach(function(p){
            path_to_watch.push(p)
          })
        }
      });
      grunt.config.set('path_to_watch', path_to_watch);
      done();
    }, true);
  });

  grunt.registerTask('watch', function() {
    var watchPaths = grunt.config.get('path_to_watch');
    if (watchPaths) {
      spawnWatchr( watchPaths )
    }
  });

  grunt.registerTask('classes-dump', function() {
    var done = this.async();
    spawnPhp('php composer.phar dumpautoload', function () {
      done();
    });
  });

  grunt.registerTask('start', function() {
    var done = this.async();
    spawnPhp('php -S localhost:8000 -t www app.php', function () {
      done();
    });
  });

  // Default task(s).
  grunt.registerTask('init', [
    'classes-dump',
    'cache-init',
    'http-init',
    'db-init'
  ]);
  grunt.registerTask('default', [
    'init',
    'fs-cache-dump',
    'open:browser',
    'watch',
    'start'
  ]);
};
