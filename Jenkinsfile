pipeline {
  agent any
  stages {
    stage('Print Message') {
      steps {
        echo 'This is a test'
        sh 'composer install'
      }
    }
    stage('Test') {
      steps {
        sh './vendor/bin/phpunit --bootstrap src/autoload.php tests'
      }
    }
  }
}