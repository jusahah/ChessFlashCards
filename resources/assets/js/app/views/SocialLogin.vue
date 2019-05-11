<template>
  <div>
    <h3>Login (using Google)</h3>
    <button v-on:click="loginWithGoogle">Login with Google</button>
  </div>
</template>

<script>

import AuthService from '@/auth/auth'
import API from '@/api'

export default {
  name: 'SocialLogin',
  data() {
    return {
      loggingin: false,
    }
  },
  mounted () {
    window.addEventListener('message', this.onMessage, false)
  },
  beforeDestroy () {
    window.removeEventListener('message', this.onMessage)
  },  
  methods: {
    onMessage(e) {

      if (!e.data || !e.data.token) {
        console.log("Unexpected msg on SocialLogin::onMessage - no token or data attribute");
        return;
      }

      if (!this.loggingin) {
        // Too late
        console.log("Unexpected msg on SocialLogin::onMessage - login flow already ended");
        return;
      }

      var apiKey = e.data.token;

      // Fetch rest of user data
      return API.auth.validateApiKey(apiKey)
      .then((response) => {
        this.loggingin = false;
        AuthService.setUserId(response.user_id);
        AuthService.setUserName(response.username);
        AuthService.setUserRole(response.role);
        AuthService.setApiKey(apiKey);
        // Move to member area
        return this.$router.replace({name: 'Dashboard'});
      })
      .catch((e) => {
        this.loggingin = false;
        alert('Api key not found on server or user is denied');
        throw e;
      })

    },
    loginWithGoogle() {
      if (this.loggingin) {
        return;
      }

      this.loggingin = true;

      return API.auth.getAuthLoginRedirect()
      .then((responseData) => {
        return responseData.url;
      })
      .then((url) => {
        // console.log("Redirect url " + url);
        // Open new window for login flow on Googles end.
        const newWindow = window.open(url, 'Mcf Google login');
        newWindow.location.href = url
        newWindow.focus();
      })
      .catch((e) => {
        this.loggingin = false;
        throw e;
      })
    }  

  },
}

</script>