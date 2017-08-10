pipeline {
    agent any

    stages {
        stage('Prepare host') {
            steps {
                checkout scm
            }
        }
        stage('Build container'){
            steps {
               docker.image('composer').inside {
                    stage("Prepare container") {
                        sh 'rm -rf ./build/{logs,pdepend} 2> /dev/null'
                        sh 'mkdir -p ./build/{logs,pdepend}'
                        sh 'chmod +x ./bin/*.sh'
                        sh 'COMPOSER_HOME=/tmp/.composer ./bin/prepare_tests.sh'
                    }

                    stage("Run tests in container") {
                        sh './bin/run_tests.sh'
                    }
                }
            }
        }
    }
}