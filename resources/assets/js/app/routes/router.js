import Vue from 'vue'
import VueRouter from 'vue-router'

import AuthService from '@/auth/auth'
import API from '@/api'

import Login from '@/views/Login'
import Member from '@/views/member/Member'
import Dashboard from '@/views/member/Dashboard'
import UploadGames from '@/views/member/upload/UploadGames'
import ProcessGames from '@/views/member/upload/ProcessGames'
import ProcessGame from '@/views/member/upload/process/ProcessGame'

Vue.use(VueRouter)

// Routes

const router = new VueRouter({
    mode: 'history',
    routes: [
      {
          path: '/',
          name: 'guest',
          component: Login
      },    
      {
          path: '/login',
          name: 'login',
          component: Login
      },
      {
          path: '/logout',
          name: 'logout',
          // No component, will redirect right away
      },  
      // Auth / member routes
      {
        path: '/auth',
        component: Member,
        children: [
          {
            path: 'dashboard',
            name: 'Dashboard',
            component: Dashboard,
          },
          {
            path: 'upload',
            name: 'UploadGames',
            component: UploadGames,
          },
          {
            path: 'process',
            name: 'ProcessGames',
            component: ProcessGames,
          },
          {
            path: 'process/:id',
            props: true,
            name: 'ProcessGame',
            component: ProcessGame,
          }

        ]
      }
    ],
});

// This is route we move to after logging in. Will be populated if client tries
// to go to a route but is not logged in; then he is first redirected to login page.
var routeAfterLogin = null;

router.beforeEach(function (to, fromRoute, next) {

  // Non-auth routes
  
  if (to.name === 'login') {
    return next();
  }

  if (to.name === 'logout') {
    AuthService.logout();
    return router.replace({name: 'login'});
  }

  // Ensure we 1) have cached api key and that 2) the key is valid.
  if (!AuthService.hasApiKey()) {
    
    console.log("No api key - must login first");

    if (to.path === '/' || !to.path) {
      // This is the dashboard view
      routeAfterLogin = '/auth/orders';
    } else {
      // Some custom view - adding this allows linking straight to order e.g. /auth/orders/7
      // User will be routed to that order page after successful login.
      routeAfterLogin = to.path;    
      
    }

    // Api key missing -> must do login flow
    return router.replace({name: 'login'});
  } else {

    if (AuthService.getUserId() === null) {

      console.log("Get user id is null - must fetch user info first");

      // Fetch user info separately.
      // This is just for cases when user is already logged (via localstorage use) when page loaded.
      return API.auth.validateApiKey(AuthService.getApiKey())
      .then((response) => {
        AuthService.setUserId(response.user_id);
        AuthService.setUserName(response.username);
        AuthService.setUserRole(response.role);
        //AuthService.setApiKey(apiKey);
        // This is bit messy, but do this here too.
        if (to.path === '/' || !to.path) {
          return next('/auth/dashboard');
        } else {
          return next();
        }       
      })
      .catch((e) => {
        AuthService.logout();
        router.replace({name: 'login'});   
        throw e;     
      })
    }

    if (to.path === '/' || !to.path) {
      return next('/auth/orders');
    }
  }

  if (routeAfterLogin) {
    var stashedRoute = routeAfterLogin;
    routeAfterLogin = null;
    return router.replace(stashedRoute);
  }
  
  return next();

});

export default router;