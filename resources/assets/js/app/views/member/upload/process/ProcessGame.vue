<template>
  <div>
    <h1>Process the game {{this.id}}</h1>


  </div>
</template>

<script>

  import API from '@/api'
  
  export default {
    name: 'ProcessGame',
    props: ['id'],
    data() {
      return {
        loading: false,
        game: null
      }
    },
    created() {
      this.loadGame();
    },
    methods: {

      loadGame() {
        this.loading = true;
        return;
        return API.process.getUnprocessedGame(this.id)
        .then((game) => {
          this.game = game;
          this.loading = false;
        })
      },

    }
  }
</script>