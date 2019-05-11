<template>
  <div>
    <h1>Dashboard</h1>
    <h3>Subheading</h3>
    <b-button variant="primary">Test</b-button>
    <b-button variant="danger">Test</b-button>
    <b-button variant="success">Test</b-button>
    <b-button variant="warning">Test</b-button>
    <b-button variant="info">Test</b-button>
  </div>
</template>

<script>

import API from '@/api'

export default {
  name: 'Dashboard',
  data() {
    return {
      users: null,
    }
  },
  created() {

  },
  methods: {
   
  }
}

</script>