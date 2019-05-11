<template>
  <!-- Development view of login -->
  <QuickLogin v-if="useQuickLogin"></QuickLogin>
  <!-- Real view of login -->
  <SocialLogin v-else></SocialLogin>
</template>

<script>

import QuickLogin from './QuickLogin'
import SocialLogin from './SocialLogin'

export default {
  name: 'Login',
  components: {
    QuickLogin,
    SocialLogin
  },
  computed: {
    useQuickLogin: function() {
      return process.env.NODE_ENV === 'development';
    }
  }
}

</script>