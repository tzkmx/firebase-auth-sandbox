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
            background-color: rgba(0, 114, 198, 0.37);
            margin: 0;
        }

        #container {
            margin-left: auto;
            margin-right: auto;
            text-align: center;
            background-color: azure;
        }

        #verifier .container {
            display: grid;
            grid-gap: 1rem;
            padding: 1rem;
            align-items: center;
            justify-items: center;
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
        <div id="verifier"></div>
        <div id="captcha-verifier"></div>
    </div>
</div>

<!-- Firebase App (the core Firebase SDK) is always required and must be listed first -->
<script src="https://www.gstatic.com/firebasejs/8.9.1/firebase-app.js"></script>
<!-- Add Firebase products that you want to use -->
<script src="https://www.gstatic.com/firebasejs/8.9.1/firebase-auth.js"></script>

<script src="https://unpkg.com/xstate@4/dist/xstate.js"></script>

<script src="https://unpkg.com/vue@3.2.19/dist/vue.global.js"></script>

<script src="https://unpkg.com/@xstate/vue@0.8.1/dist/xstate-vue.js"></script>
<!--<div class="card-content bg-white">
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
    </div>
</div>-->
<script>
  const firebaseConfig = {
    apiKey: "<?= $_ENV['FIREBASE_API_KEY'] ?>",
    authDomain: "samaya-260a0.firebaseapp.com",
    databaseURL: "https://samaya-260a0.firebaseio.com",
    projectId: "samaya-260a0",
    storageBucket: "samaya-260a0.appspot.com",
    messagingSenderId: "745535016439",
    appId: "1:745535016439:web:bb5ee31fb71f06307572f2",
    measurementId: "G-DM8P0LHHEJ"
  }

  const firebaseApp = firebase.initializeApp(firebaseConfig)
  firebase.auth().useDeviceLanguage()

  const captchaVerifier = new firebase.auth
    .RecaptchaVerifier('captcha-verifier', {
        'size': 'invisible',
        'callback': function (response) {
          onSignInSubmit()
        }
      }
    )

  const {assign, createMachine} = XState

  const verifyPhoneMachine = createMachine(
    {
      id: "phoneVerificator",
      initial: "notAccepted",
      strict: false,
      context: {
        phoneNumber: "",
        confirmator: "",
        confirmCode: "",
        error: null,
        user: null,
        idToken: null
      },
      states: {
        notAccepted: {
          on: {
            TOGGLE_TERMS: {target: 'acceptedTerms.hist'}
          }
        },
        acceptedTerms: {
          initial: "capturingPhone",
          on: {
            TOGGLE_TERMS: {target: 'notAccepted'}
          },
          states: {
            hist: {type: 'history'},
            capturingPhone: {
              on: {
                CAPTURE_PHONE: {
                  actions: ["setPhoneNumber"]
                },
                REQUEST_SMS: {
                  target: "requestingSms",
                  cond: "nonEmptyPhone"
                }
              }
            },
            requestingSms: {
              invoke: {
                id: "requestSms",
                src: requestSms,
                onDone: {
                  target: "waitingConfirm",
                  actions: assign({confirmator: (ctx, ev) => ev.data})
                },
                onError: {
                  target: "errorSms",
                  actions: assign({error: (ctx, ev) => ev.data})
                }
              }
            },
            waitingConfirm: {
              on: {
                CAPTURE_CONFIRM: {
                  actions: ["setConfirmCode"]
                },
                REQUEST_CONFIRM: {
                  target: "sendingConfirm",
                  cond: "nonEmptyConfirm"
                }
              }
            },
            sendingConfirm: {
              invoke: {
                id: "sendingConfirm",
                src: confirmSmsCode,
                onDone: {
                  target: "confirmed",
                  actions: assign({user: (ctx, ev) => ev.data})
                },
                onError: {
                  target: "errorConfirm",
                  actions: assign({error: (ctx, ev) => ev.data})
                }
              }
            },
            confirmed: {
              on: {
                always: {
                  target: "requestingToken"
                }
              }
            },
            requestingToken: {
              invoke: {
                id: "requestIdToken",
                src: requestIdToken,
                onDone: {
                  target: "readyToAuthorize",
                  actions: assign({idToken: (ctx, ev) => ev.data})
                },
                onError: {
                  target: "errorGetToken",
                  actions: assign({error: (ctx, ev) => ev.data})
                }
              }
            },
            readyToAuthorize: {
                on: {
                  always: {
                    target: "settingAuthorization"
                  }
                }
            },
            settingAuthorization: {
              invoke: {
                id: "requestIdToken",
                src: requestIdToken,
                onDone: {
                  target: "readyToAuthorize",
                  actions: assign({idToken: (ctx, ev) => ev.data})
                },
                onError: {
                  target: "errorGetToken",
                  actions: assign({error: (ctx, ev) => ev.data})
                }
              }
            },
            ready: {
              type: "final"
            },
            errorSms: {
              on: {
                RETRY: {target: 'capturingPhone'}
              }
            },
            errorConfirm: {
              on: {
                RETRY: {target: 'waitingConfirm'}
              }
            },
            errorGetToken: {
              on: {
                RETRY: {target: 'requestingToken'}
              }
            }
          }
        }
      }
    },
    {
      actions: {
        setPhoneNumber: assign({
          phoneNumber: (ctx, event) => event.phoneNumber
        }),
        setConfirmCode: assign({
          confirmCode: (ctx, event) => event.confirmCode
        })
      },
      guards: {
        nonEmptyPhone: (ctx) => ctx.phoneNumber.trim() !== "",
        nonEmptyConfirm: (ctx) => ctx.confirmCode.trim() !== ""
      }
    }
  )

  const ViewApiKey = {
    props: {
      apiKey: String
    },
    template: `<p>Ready!</p>
  <p>KEY: {{ apiKey }}</p>`
  }

  const CapturePhone = {
    props: {
      accepted: Boolean,
      send: Function,
      phoneNumber: String
    },
    methods: {
      setPhoneNumber(ev) {
        this.send({type: "CAPTURE_PHONE", phoneNumber: ev.target.value});
      }
    },
    template: `<div class="container">
    <p>Por favor proporcione su número de teléfono para continuar</p>
    <input :value="phoneNumber" @input="setPhoneNumber" :disabled="!accepted" />
    <div style="display: flex">
    <input id="terms" type="checkbox" v-on:click="send('TOGGLE_TERMS')" />
    <label for="terms" v-if="accepted">Acepta términos y condiciones</label>
    <label for="terms" v-if="!accepted">Debe aceptar términos y condiciones para continuar</label>
    </div>
    <button v-on:click="send('FETCH')" v-if="accepted">Verificar</button>
    </div>`
  }

  const Verification = {
    components: {
      CapturePhone,
      ViewApiKey
    },
    setup() {
      const {send, state} = XStateVue.useMachine(verifyPhoneMachine)
      return {send, state}
    },
    methods: {
      retry() {
        this.send("RETRY");
      }
    },
    template: `
  <p v-if="state.matches('error')">Error: {{ state.context.error }}</p>
  <view-api-key v-if="state.matches('ready')"
    :api-key="state.context.apiKey" />
  <p v-if="state.matches('ready')">Result: {{ state.context.result }}</p>
  <capture-phone v-if="state.value !== 'ready'"
    :accepted="!state.matches('notAccepted')"
    :send="send" :phone-number="state.context.phoneNumber" />
  <button v-if="state.matches('error')"
    @click="retry">Retry</button>
  `
  }

  const app = Vue.createApp(Verification)
  app.mount("#verifier")

  function requestSms(ctx) {
    const { phoneNumber } = ctx

    return firebase.auth()
        .signInWithPhoneNumber(phoneNumber, captchaVerifier)
  }

  function confirmSmsCode(ctx) {
    const { confirmCode, confirmator } = ctx

    return confirmator.confirm(confirmCode)
  }

  function requestIdToken() {
    return firebase.auth()
      .currentUser.getIdToken(true)
  }
</script>
</body>