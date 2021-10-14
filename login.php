<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

?><!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body {
            color: #000000;
            background-color: #0072C6;
            margin: 0;
        }

        #container {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        .hidden-confirm {
            background-color: aqua;
        }
        .hidden-confirm * {
            border-color: red;
        }
    </style>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://unpkg.com/@tailwindcss/custom-forms@0.2.1/dist/custom-forms.css" rel="stylesheet">
</head>
<body class="grey darken-3">
<div id="container p-6">
    <div class="col l4 offset-l4 m6 offset-m3 s12" id="authCard">
        <div>
            <ul class="tabs">
                <li class="tab col s3 waves-effect"><a href="#registerSection" class="active">Register</a></li>
                <li class="tab col s3 waves-effect"><a href="#loginSection">Login</a></li>
            </ul>
            <div class="card-content bg-white">
                <div class="flex flex-col">
                    <form action="" id="authPhone" class="p-6">
                        <fieldset class="flex flex-col">
                            <legend>Phone</legend>
                            <label for="phone">Enter your Phone</label>
                            <input type="text" id="phone" class="form-input"
                                   autocomplete="off"/>
                            <div id="sign-in-button">hmmm</div>
                            <button type="submit" class="button">Login Now
                            </button>
                        </fieldset>
                    </form>
                    <form action="" id="confirmCode" class="hidden-confirm">
                        <fieldset class="flex">
                            <label>Su código de confirmación
                                <input type="text" id="confirm-code" class="form-input">
                            </label>
                            <button type="submit" class="button">Confirmar</button>
                        </fieldset>
                    </form>
                    <form action="" id="authRegister" class="p-6">
                        <fieldset class="flex flex-col">
                            <legend>Register</legend>
                            <label for="register_email">Enter your Email</label>
                            <input type="email" id="register_email" class="form-input"
                                   autocomplete="off"/>
                            <label for="register_password">Enter your Password</label>
                            <input type="password" id="register_password" class="form-input"/>
                            <button type="submit" class="button">Register Now
                            </button>
                        </fieldset>
                    </form>
                    <form action="" id="authLogin" class="p-6">
                        <fieldset class="flex flex-col">
                            <legend>Login</legend>
                            <label for="login_email">Enter your Email</label>
                            <input type="email" id="login_email" class="form-input"
                                   autocomplete="off"/>
                            <label for="login_password">Enter your Password</label>
                            <input type="password" id="login_password" class="form-input"/>
                            <button type="submit" class="button">Login Now
                            </button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="root" class="row">
    <div class="col l4 offset-l4 m6 offset-m3 s12">
        <div class="card">
            <div class="card-content" id="firebase-token">

            </div>
        </div>
    </div>
</div>
<!-- Insert these scripts at the bottom of the HTML, but before you use any Firebase services -->

<!-- Firebase App (the core Firebase SDK) is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.9.1/firebase-app.js"></script>

<!-- If you enabled Analytics in your project, add the Firebase SDK for Analytics -->
<script src="https://www.gstatic.com/firebasejs/8.9.1/firebase-analytics.js"></script>

<!-- Add Firebase products that you want to use -->
<script src="https://www.gstatic.com/firebasejs/8.9.1/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.9.1/firebase-firestore.js"></script>
<script>
;(function (document) {
  var $$ = (qs) => document.querySelectorAll.apply(document, [qs])
  var $ = (qs) => document.querySelector.apply(document, [qs])
  document.addEventListener("DOMContentLoaded", function (el) {
    console.log({el})
    // Handler when the DOM is fully loaded

    // TODO: Replace the following with your app's Firebase project configuration
    // For Firebase JavaScript SDK v7.20.0 and later, `measurementId` is an optional field
    var firebaseConfig = {
      apiKey: "<?= $_ENV['FIREBASE_API_KEY'] ?>",
      authDomain: "samaya-260a0.firebaseapp.com",
      databaseURL: "https://samaya-260a0.firebaseio.com",
      projectId: "samaya-260a0",
      storageBucket: "samaya-260a0.appspot.com",
      messagingSenderId: "745535016439",
      appId: "1:745535016439:web:bb5ee31fb71f06307572f2",
      measurementId: "G-DM8P0LHHEJ"
    }

    // Initialize Firebase

    const app = firebase.initializeApp(firebaseConfig)
    firebase.auth().useDeviceLanguage()

    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('sign-in-button', {
      'size': 'invisible',
      'callback': function(response) {
        onSignInSubmit()
      }
    })

    // const analytics = firebase.getAnalytics(app)

    firebaseToken = $("#firebase-token")
    firebaseToken.innerHTML = "Welcome hola !"

    $('#authRegister').addEventListener('submit', authRegister)
    $('#authLogin').addEventListener('submit', authLogin)
    $('#authPhone').addEventListener('submit', authPhone)
    $('#confirmCode').addEventListener('submit', confirmCode)

    // User SignUp
    function authRegister(event) {
      event.preventDefault()
      var email = $('#register_email').value
      var password = $('#register_password').value

      firebase
        .auth()
        .createUserWithEmailAndPassword(email, password)
        .then(function (r) {
          console.log({ r })
          firebaseToken.innerHTML = "Registered successfully !"
        })
        .catch(function (err) {
          console.warn({ err })
          alert(err.message)
        })
    }

    // User SignIn
    function authLogin(event) {
      event.preventDefault()
      var login_email = $('#login_email').value
      var login_password = $('#login_password').value

      firebase
        .auth()
        .signInWithEmailAndPassword(login_email, login_password)
        .then(function (r) {
          console.log({r})
          firebaseToken.innerHTML = "Sign-in Successful !"
          console.log('sign in successful !')
        })
        .catch(function (err) {
          console.warn({err})
          alert(err.message)
        })
    }

    function authPhone(event) {
      event.preventDefault()
      var phone = $('#phone').value

      var appVerifier = window.recaptchaVerifier

      firebase
        .auth()
        .signInWithPhoneNumber(phone, appVerifier)
        .then((result) => {
          console.log({ result })
          window.confirmationResult = result
          /** @var DocumentElement confirmCodeElem */
          var confirmCodeElem = $('#confirmCode')
          confirmCodeElem.classList.remove('hidden-confirm')
        })
        .catch(err => {
          console.warn({ err })
        })
    }

    function confirmCode(event) {
      event.preventDefault()
      var confirmationInput = $('#confirm-code').value
      confirmationResult.confirm(confirmationInput)
        .then((result) => {
          console.log({ result })
          firebaseToken.innerHTML = result.user.toString()
        })
      .catch(err => {
        console.warn({ err })
      })
    }
  })

  function verifyIdToken(token) {
    var data = new FormData()
    data.set('idToken', token)
    fetch('verify.php', {
      method: 'POST',
      body: data
    })
      .then(r => r.json())
      .then(console.log)
      .catch(console.warn)
  }
  window.verifyIdToken = verifyIdToken
})(document)
</script>

</body>
</html>