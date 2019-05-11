
<template>
  <!-- This template works as a wrapper for member-scoped views. -->
  <!-- It also defines basic skeleton of the app -->

  <div id="mcf-member" style="position: relative;">
    <Navigation></Navigation>
    <p style="font-size: 11px; position: absolute; top: -20px; right: 4px;">
      Logged in as <i>{{username}}</i> with role <i>{{role}}</i></p>
    <br>
    <b-row>
      <b-col>
        <b-card>
          <!-- Main content area -->
          <router-view></router-view>
        </b-card>
      </b-col>
    </b-row> 
    <br>

    <!-- Confirmation modal (used everywhere we need to ask for confirmation from user) -->
    <b-modal 
      id="confirmationmodal" 
      title="Confirm operation"
      v-model="modalShow"
      @ok="confirmOperation"
      @cancel="cancelOperation"
    >
      <p class="my-4" v-html="modalText"></p>
    </b-modal>
    
  </div>
</template>


<script>

import Promise from 'bluebird'
import API from '@/api'

import AuthService from '@/auth/auth'
import EventBus from '@/services/eventbus';

import Navigation from '@/navigation/navigation'

export default {
  name: 'Member',
  components: {
    Navigation
  },
  data() {
    return {
      // Other
      modalShow: false,
      modalText: '',
      username: null,
      role: '???'
      // Commented as we do not want Vue auto-tracking these
      // confirmCallback: null,
      // eventBusCallback: null,

    }
  },
  created() {
    this.username = AuthService.getUserName();
    this.role = AuthService.getRole();
    // We don't want callback value of confirmation asking to be tracked by Vue
    // Thus we add it only now to data-object
    this.confirmCallback = null;
    this.eventBusCallback = (question, confirmCallback) => {
      // Open modal with provided question text
      this.modalText = question;
      this.modalShow = true;
      this.confirmCallback = confirmCallback;
    }

    // Opening modal is done by other Vue components via event bus.
    // Register listener listening opening requests
    EventBus.$on('ask-confirmation', this.eventBusCallback);
    
  },
  beforeDestroy() {
    // Stop listening for confirmation requests
    EventBus.$off('ask-confirmation', this.eventBusCallback);
  },
  methods: {
    confirmOperation(evt) {

      if (this.confirmCallback) {
        this.confirmCallback()
      }

    },
    cancelOperation() {
      this.confirmCallback = null;
    },  
  }
}


</script>