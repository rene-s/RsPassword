pipeline {
  agent any

  stages {
    stage('Checkout') {
      steps {
        checkout scm
        sh 'rm -rf ./build/{logs,pdepend}'
        sh 'mkdir -p ./build/{logs,pdepend}'
        sh './bin/prepare_tests.sh'
      }
    }

    stage('Unit tests') {
      steps {
        sh './bin/run_tests.sh'
      }

      post {
        success {
          step(
            [
              $class: 'XUnitBuilder',
              testTimeMargin: '3000',
              thresholdMode: 1,
              thresholds: [[
                $class: 'FailedThreshold',
                failureNewThreshold: '',
                failureThreshold: '',
                unstableNewThreshold: '',
                unstableThreshold: ''
              ],
              [
                $class: 'SkippedThreshold',
                failureNewThreshold: '',
                failureThreshold: '',
                unstableNewThreshold: '',
                unstableThreshold: ''
              ]],
              tools: [[
                $class: 'PHPUnitJunitHudsonTestType',
                deleteOutputFiles: true,
                failIfNotNew: true,
                pattern: 'build/logs/junit.xml',
                skipNoTestFiles: false,
                stopProcessingIfError: true
              ]]
            ]
          )
        }
      }
    }
  }
}