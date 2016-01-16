module.exports = function(grunt) {

    grunt.initConfig({

        requirejs : {
            default : {
                options : {
                    baseUrl : 'src/js',
                    mainConfigFile : 'src/js/default/config.js',
                    findNestedDependencies : true,
                    name : 'src/js/main.js',
                    out : 'dist/js/main.js',
                    generateSourceMaps : true,
                    preserveLicenseComments : false,
                    optimize : 'uglify2'
                }
            }
        },

        compass : {
            default : {
                options : {
                    require : 'compass/import-once/activate',

                    sassDir : 'src/sass',
                    cssDir : 'dist/css',

                    imagesDir : 'dist/images',
                    fontsDir : 'dist/fonts',

                    relativeAssets : true,
                    // force : true,
                    sourcemap : true
                }
            }
        },

        postcss : {
            default : {
                options : {
                    map : {
                        prev : 'dist/main.css.map'
                    },
                    processors : [require('autoprefixer-core')({
                        browsers : 'last 1 version'
                    })]
                },
                src : 'dist/main.css'
            }
        },

        watch : {
            scripts : {
                files : ['src/js/**/*.js', 'vendor/**/*.js'],
                tasks : ['js'],
                options : {
                    spawn : false,
                }
            },
            styles : {
                files : ['src/sass/**/*.scss', 'vendor/**/*.scss'],
                tasks : ['css'],
                options : {
                    spawn : false
                }
            }
        }

    });

    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-postcss');
    grunt.loadNpmTasks('grunt-contrib-watch');
    
    grunt.registerTask('default', ['js', 'css']);
    grunt.registerTask('js', ['requirejs:default']);
    grunt.registerTask('css', ['compass:default', 'postcss:default']);
    
};
