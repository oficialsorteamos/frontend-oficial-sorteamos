import useJwt from '@/auth/jwt/useJwt'
import Echo from 'laravel-echo'
import router from '@/router'
import Vue from 'vue'
/**
 * Return if user is logged in
 * This is completely up to you and how you want to store the token in your frontend application
 * e.g. If you are using cookies to store the application please update this function
 */
// eslint-disable-next-line arrow-body-style
export const isUserLoggedIn = () => {

  //console.log(window.localStorage.getItem('accessToken'));
  // remove token if cookie not set
  if(getSpecificCookie("loginstatus") == 'loginstatus=loggedin') {
    if(process.env.MIX_APP_ENV == 'production') {
      window.Pusher = require('pusher-js');
      //Inicializa o Laravel Echo
      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        //authEndpoint: '/broadcasting/auth',
        wsHost: 'ws.'+window.location.hostname,
        //wsPort: 6001,
        //wssPort: 6001,
        wsPort: 443,
        wssPort: 443,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        //forceTLS: false,
        forceTLS: true,
        disableStats: true, 
        enabledTransports: ['ws', 'wss'],
        //encrypted: false,
        auth: {
            headers: {
                'Authorization': 'Bearer ' + window.localStorage.getItem('accessToken'),
                'X-CSRF-Token': "CSRF_TOKEN"
            }
        }
      });
    } else {
      window.Pusher = require('pusher-js');
      //Inicializa o Laravel Echo
      window.Echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.MIX_PUSHER_APP_KEY,
        //authEndpoint: '/broadcasting/auth',
        wsHost: window.location.hostname,
        wsPort: 6001,
        //wssPort: 6001,
        cluster: process.env.MIX_PUSHER_APP_CLUSTER,
        forceTLS: false,
        disableStats: true,
        enabledTransports: ['ws'],
        //encrypted: false,
        auth: {
            headers: {
                'Authorization': 'Bearer ' + window.localStorage.getItem('accessToken'),
                'X-CSRF-Token': "CSRF_TOKEN"
            }
        }
      });
    }
    //Cria uma variável de sessão com os dados do usuário logado
    Vue.prototype.$userData = localStorage.getItem('userData');
    
    return localStorage.getItem('userData') && localStorage.getItem(useJwt.jwtConfig.storageTokenKeyName)
  } //Se o usuário fechou o navegador e abriu novamente
  else {
    if(localStorage.getItem('userData') != null) {
      
      //Remove as credenciais de acesso do usuário
      localStorage.removeItem(useJwt.jwtConfig.storageTokenKeyName)
      localStorage.removeItem(useJwt.jwtConfig.storageRefreshTokenKeyName)

      localStorage.removeItem("userData")

      //Atauliza a página para que o usuário possa ser direcionado para a página de login
      window.location.reload()
    }
  }
}

export const getUserData = () => JSON.parse(localStorage.getItem('userData'))

/**
 * This function is used for demo purpose route navigation
 * In real app you won't need this function because your app will navigate to same route for each users regardless of ability
 * Please note role field is just for showing purpose it's not used by anything in frontend
 * We are checking role just for ease
 * NOTE: If you have different pages to navigate based on user ability then this function can be useful. However, you need to update it.
 * @param {String} userRole Role of user
 */
/*
export const getHomeRouteForLoggedInUser = userRole => {
  if (userRole === 'admin') return '/'
  if (userRole === 'client') return { name: 'access-control' }
  return { name: 'auth-login' }
}
*/
//Caso o usuário tenha perfil de administrador, leva para raiz do sistema, caso sejá operador, redireciona para o chat
export const getHomeRouteForLoggedInUser = userRole => {
  // import Laravel Echo
  require('../../../resources/js/bootstrap.js')
  //Carrega as funções do webpush
  require('../../../public/js/enable-push.js')
  
  document.cookie = "loginstatus=loggedin"
  
  //Caso perfil seja de GESTOR ou ADMINISTRADOR
  if (userRole === 1 || userRole === 3) return '/'
  //Caso o perfil seja de OPERADOR
  if (userRole === 2) return { name: 'apps-chat' }
  //Caso o perfil seja de White Label
  if (userRole === 4) return { name: 'apps-administration-companies' }
  return { name: 'auth-login' }
}

function getSpecificCookie(cookieName, valueOnly) {
  //Get original cookie string
  var oCookieArray = document.cookie.split(';'),
      fc,
      cnRE = new RegExp(cookieName + '\=');
  //Loop through cookies
  for (var c = 0; c < oCookieArray.length; c++) {

      //If found save to variable and end loop
      if (cnRE.test(oCookieArray[c])) {
          fc = oCookieArray[c].trim();
          if (valueOnly) {
              fc = fc.replace(cookieName +'=', '');
          }
          break;
      }

  }
  return fc;
}
