var exec = require('child_process').exec;
var watchr = require('watchr');
var async = require('async');

module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    dumps_file_path: [],

    shell: {
      init: {
        command: 'php bootstrap.php --event init.app'
      },
      dump: {
        command: 'php bootstrap.php --event dump.fs'
      },
      initDb: {
        command: 'php bootstrap.php --event init.schema'
      },
      checkDb: {
        command: 'php bootstrap.php --event check.schema'
      },
      options: {
        stdout: true,
        stderr: true,
        failOnError: true
      }
    },

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
  grunt.loadNpmTasks('grunt-shell-spawn');

  grunt.registerTask('get-fs-dumps', function() {
    var done = this.async();

    var dumps_file_path = [];
    var path_to_watch = [];

    async.series([
      function(next){
        exec('php bootstrap.php --event dump.fs_file_path',
          function (error, stdout, stderr) {
            dumps_file_path = stdout.replace(/\s*$/, '').split('\n');
            next(error);
          });
      },
      function(next){
        var p = []
        dumps_file_path.forEach(function(k) {
          p.push(function(next_){
            exec('php -r "echo json_encode(include(\\"' + k + '\\"));"',
              function (error, stdout, stderr) {
                var data = JSON.parse(stdout);
                if (data.originalPaths.length) {
                  path_to_watch = path_to_watch.concat(data.originalPaths)
                } else {
                  Object.keys(data.items).forEach(function(p){
                    path_to_watch.push(p)
                  })
                }
                next_(error);
              });
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
    var killed = false;
    var child = exec('php -S localhost:8000 -t www bootstrap.php', { stdio: 'pipe' },
      function (error) {
        if (!killed && error !== null) {
          console.log('exec error: ' + error);
        }
        done();
      });
    child.stdout.on('data', function (d){
      grunt.log.warn(d)
    });
    child.stderr.on('data', function (d){
      grunt.log.error(d)
    });
    process.on('SIGINT', function() {
      killed = true;
      child.kill();
    });
    process.on('exit', function() {
      child.kill();
    });

    var watchPaths = grunt.config.get('path_to_watch');
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
          exec('grunt shell:dump', function (error, stdout, stderr) {
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
  });

  // Default task(s).
  grunt.registerTask('init', [
    'shell:init'
  ]);
  grunt.registerTask('default', [
    'shell:dump',
    //'shell:initDb',
    'shell:checkDb',
    'get-fs-dumps',
    //'open:browser',
    'spawn-builtin'
  ]);
};
