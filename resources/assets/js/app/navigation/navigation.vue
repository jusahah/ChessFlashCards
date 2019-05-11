<template>
  <b-navbar sticky toggleable="sm" variant="warning">

    <b-navbar-toggle target="nav_collapse"></b-navbar-toggle>

    <b-navbar-brand :to="{name: 'Dashboard'}">ChessTraining</b-navbar-brand>

    <b-collapse is-nav id="nav_collapse">

      <b-navbar-nav>
        <b-nav-item :to="{name: 'Dashboard'}">Upload</b-nav-item>
        <b-nav-item :to="{name: 'Dashboard'}">Train</b-nav-item>
        <b-nav-item :to="{name: 'Dashboard'}">View</b-nav-item>
        <b-nav-item :to="{name: 'Dashboard'}">Settings</b-nav-item>
      </b-navbar-nav>

      <!-- Right aligned nav items -->
      <b-navbar-nav class="ml-auto">
        <b-nav-item :to="{name: 'logout'}">Logout</b-nav-item>
      </b-navbar-nav>

    </b-collapse>
  </b-navbar>
</template>


<script>

import AuthService from '@/auth/auth'

export default {
  name: 'Navigation',

  data() {
    return {

    }
  },

  created() {
    

  }
}


</script>