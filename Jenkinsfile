pipeline {
  agent {
    node {
      label 'rsPasswordTest'
    }
    
  }
  stages {
    stage('TestStage') {
      steps {
        sh 'ls -l /tmp'
      }
    }
  }
}