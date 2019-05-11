<template>
  <!-- Development view of login. Real use has links to server routes taking caring of OAuth.-->
  <div>
    <h3>Login (development view)</h3>
    <p style="font-style: italic;">
      Get API key (of any User) from database (using phpmyadmin or similar tool), and paste it here.
    </p>
    <input type="text" v-model="apikey" />
    <button v-on:click="addApiKey">Fake login</button>
  </div>
</template>

<script>

import AuthService from '@/auth/auth'
import API from '@/api'

export default {
  name: 'QuickLogin',
  data() {
    return {
      validating: false,
      apikey: '123456'
    }
  },  
  methods: {
    addApiKey() {
      // Validate in the server
      
      var apiKey = this.apikey;

      this.validating = true;
      return API.auth.validateApiKey(apiKey)
      .then((response) => {
        console.log(response);
        this.validating = false;
        AuthService.setUserId(response.user_id);
        AuthService.setUserName(response.username);
        AuthService.setUserRole(response.role);
        AuthService.setApiKey(apiKey);
        return this.$router.replace({name: 'Dashboard'});
      })
      .catch((e) => {
        this.validating = false;
        throw e;
      })

    }

  },
}

</script>