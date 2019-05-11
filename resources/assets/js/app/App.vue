<template>
  <b-container>
    <!-- This alert will serve most error notification purposes for the UI -->
    <b-alert :variant="alertMsgType"
     style="position: fixed; bottom: 0; left: 0; width: 100%; z-index: 9999; margin: 0; text-align: center;"
     dismissible
     :show="showDismissibleAlert"
     @dismissed="showDismissibleAlert=false"
    >
      <h5><strong>{{alertMsgType === 'danger' ? 'Error' : 'Msg'}} from server:</strong> {{alertMsg}}</h5>
    </b-alert>
    <router-view></router-view>
    <hr>
    <p style="font-size: 12px; color: #999;">
      <i>This {{envName}} environment - built at {{builtAt}}</i>
    </p>
    <!--
    <b-alert dismissible show>Welcome to Mcf-Domain application <i>(updated: 19.4.18, build: {{getEnv()}})</i></b-alert>
    -->
  </b-container>
</template>

<script>

import AuthService from '@/auth/auth'
import EventBus from '@/services/eventbus';

export default {
  name: 'App',
  data() {
    return {
      envName: process.env.MIX_LARAVEL_ENV || '???',
      builtAt: WP_TIMESTAMP, // Compile-time constant, generated in webpack config
      showDismissibleAlert: false,
      alertMsg: null,
      alertMsgType: 'danger'
      // Commented out to prevent tracking
      // eventBusErrorCallback: null

    }
  },
  created() {
    // We don't want callback value of error notifications to be tracked by Vue
    this.eventBusErrorCallback = (msg) => {
      console.log("Error: " + msg);
      this.alertMsg = msg;
      this.alertMsgType = 'danger';
      this.showDismissibleAlert = true;

      // No need to auto-close it after N secs.
      // Alert will be closed by user who clicks x
    }
    this.eventBusSuccessCallback = (msg) => {
      console.log("Success: " + msg);
      this.alertMsg = msg;
      this.alertMsgType = 'success';
      this.showDismissibleAlert = true;

      // No need to auto-close it after N secs.
      // Alert will be closed by user who clicks x
    }
    this.eventBusErrorClearCallback = () => {
      this.alertMsg = null;
      this.showDismissibleAlert = false;
    }
    EventBus.$on('show-success', this.eventBusSuccessCallback);
    EventBus.$on('show-error', this.eventBusErrorCallback);
    EventBus.$on('clear-error', this.eventBusErrorClearCallback);
  },
  beforeDestroy() {
    // Stop listening for error notifications
    EventBus.$off('show-success', this.eventBusSuccessCallback);
    EventBus.$off('show-error', this.eventBusErrorCallback);
    EventBus.$off('clear-error', this.eventBusErrorClearCallback);
  },

  methods: {
    getEnv() {
      return process.env.NODE_ENV;
    }
  }
}

</script>