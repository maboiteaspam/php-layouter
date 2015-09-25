var exec = require('child_process').exec;
var watchr = require('watchr');
var async = require('async');

module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    dumps_file_path: [],

    open : {
      browser : {
        path: 'http://127.0.0.1:8000/',
        app: 'firefox'
      },
      options: {
        delay: 3000
      }
    }
  });

  // Load the plugin that provides the "uglify" task.
  grunt.loadNpmTasks('grunt-open');

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
    process.on('SIGINT', function() {
      killed = true;
      child.kill();
    });
    process.on('exit', function() {
      child.kill();
    });
    return child;
  };
  var spawnWatchr = function (watchPaths) {
    grunt.log.ok('Watching paths');
    grunt.log.writeflags(watchPaths)
    watchr.watch({
      paths: watchPaths,
      ignoreInitial: true,
      persistent: true,
      delay: 1500,
      catchupDelay: 750,
      listeners: {
        log: function(logLevel){
          grunt.verbose.writeln('a log message occured:', arguments);
        },
        error: function(err){
          grunt.log.error('an error occured:', err);
        },
        watching: function(err, watcherInstance, isWatching){
          if (err) {
            grunt.verbose.error("watching the path " + watcherInstance.path + " failed with error", err);
          } else {
            grunt.verbose.ok("watching the path " + watcherInstance.path + " completed");
          }
        },
        change: function(changeType, filePath){
          grunt.log.ok('%s %s', changeType, filePath);
          exec('grunt dump-fs', function (error, stdout, stderr) {
            grunt.log.ok('updated dumps');
          });
        }
      },
      next: function(err,watchers){
        if (err) {
          console.log(err.stack)
          return grunt.log.error("watching everything failed with error", err);
        } else {
          grunt.verbose.ok('watching everything completed', watchers);
        }
      }
    });
  };

  grunt.registerTask('db-init', function() {
    var done = this.async();
    spawnPhp('php cli.php db:init', function (error) {
      done(error);
    });
  });
  grunt.registerTask('fs-init', function() {
    var done = this.async();
    spawnPhp('php cli.php fs:init', function (error) {
      done(error);
    });
  });
  grunt.registerTask('check-schema', function() {
    var done = this.async();
    spawnPhp('php cli.php db:refresh', function (error) {
      done(error);
    });
  });
  grunt.registerTask('reveal-fs-dumps', function() {
    var done = this.async();

    var dumps_file_path = [];
    var path_to_watch = [];

    async.series([
      function(next){
        spawnPhp('php cli.php fs:reveal', function (error, stdout, stderr) {
          dumps_file_path = stdout.replace(/\s*$/, '').split('\n');
          next(error);
        });
      },
      function(next){
        var p = [];
        dumps_file_path.forEach(function(k) {
          p.push(function(next_){
            spawnPhp('php -r "echo json_encode(include(\\"' + k + '\\"));"', function (error, stdout, stderr) {
              var data = JSON.parse(stdout);
              if (data.config.paths.length) {
                path_to_watch = path_to_watch.concat(data.config.paths)
              } else {
                Object.keys(data.items).forEach(function(p){
                  path_to_watch.push(p)
                })
              }
              next_(error);
            }, true);
          })
        });
        async.parallelLimit(p, 2, next);
      }
    ], function (error) {
      grunt.config.set('path_to_watch', path_to_watch);
      done(error);
    });

  });

  grunt.registerTask('spawn-builtin', function() {
    var done = this.async();
    spawnPhp('php -S localhost:8000 -t www app.php', function () {
      done();
    });
    var watchPaths = grunt.config.get('path_to_watch');
    if (watchPaths) {
      spawnWatchr( watchPaths )
    }
  });

  // Default task(s).
  grunt.registerTask('init', [
    'fs-init','db-init'
  ]);
  grunt.registerTask('default', [
    'init',
    'reveal-fs-dumps',
    //'open:browser',
    'spawn-builtin'
  ]);
};
