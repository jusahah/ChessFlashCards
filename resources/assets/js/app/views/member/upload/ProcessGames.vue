<template>
  <div>
    <h1>Process uploaded games</h1>
    <p>Every game you upload must be processed or rejected by you. </p>
    <p>Processing is done by marking key positions and/or moves of the game. When you add new games later, the positions/moves marked earlier will be searched from your new games. Moves you mark as bad will also be included to your <i>training</i> set.</p>
    <p>Rejecting the game prevents it from being tracked. The game will not be saved into database if you reject it.</p>

    <p style="color: #17a2b8;"><i>You have {{unprocessedCount}} unprocessed games!</i></p>

    <b-table v-if="unprocessedGames" striped hover :fields="fields" :items="unprocessedGames">
      <template slot="created_at" slot-scope="data">
        {{ data.item.created_at | prettytime }}
      </template>  
      <template slot="white" slot-scope="data">
        {{ data.item.white ? data.item.white.substring(0, 16) : '???'}}
      </template>
      <template slot="black" slot-scope="data">
        {{ data.item.black ? data.item.black.substring(0, 16) : '???'}}
      </template>
      <template slot="result" slot-scope="data">
        {{ data.item.result ? data.item.result : '???' }}
      </template>

      <template slot="pgn" slot-scope="data">
        {{ data.item.pgn.substring(0, 6) }}
      </template>
      <template slot="process" slot-scope="data">
        <b-button v-on:click="processGame(data.item.id)" variant="info" size="sm">Process</b-button>
      </template>       
      <template slot="reject" slot-scope="data">
        <b-button v-on:click="rejectGame(data.item.id)" variant="danger" size="sm">Reject</b-button>
      </template>      
    </b-table>

  </div>
</template>

<script>

  import API from '@/api'
  
  export default {
    bame: 'ProcessGames',
    data() {
      return {
        loading: false,
        unprocessedGames: null,
        unprocessedCount: 0,
        fields: ['game_set_id', 'white', 'black', 'result', 'created_at', 'pgn', 'process', 'reject']
      }
    },
    created() {
      this.loadGames();
    },
    methods: {

      loadGames() {
        this.loading = true;

        // TODO: Pagination.

        return API.process.getUnprocessedGames()
        .then((games) => {

          // console.warn(games);

          this.unprocessedGames = games;
          this.unprocessedCount = games.length;

          this.loading = false;
        })
      },

      processGame(id) {
        this.$router.push({name: 'ProcessGame', params: {id: id}});       
      },
      rejectGame(id) {
        var now = Date.now();
        console.log("Game rejection requested" + Date.now())
        return API.process.rejectGame(id)
        .then(() => {
          console.log("Game rejected, took: " + (Date.now() - now) + " ms");
          return this.loadGames();
        })

      }

    }
  }
</script>