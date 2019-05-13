<template>
  <div>
    <h1>Process the game {{id}}</h1>

    <b-row>
      <b-col cols="2">
        <div v-if="currentMove" style="margin-top: 30px;">
          <p>Mark {{currentMove}} as: </p>
          <b-button v-on:click="markCurrentMove(-2)" variant="danger" style="width: 100%; margin-bottom: 4px;">Terrible</b-button>
          <br>
          <b-button v-on:click="markCurrentMove(-1)" variant="danger" style="width: 100%; margin-bottom: 4px;">Bad</b-button>
          <br>
          <b-button v-on:click="markCurrentMove(0)" variant="secondary" style="width: 100%; margin-bottom: 4px;">Neutral</b-button>
          <br>
          <b-button v-on:click="markCurrentMove(1)" variant="success" style="width: 100%; margin-bottom: 4px;">Good</b-button>
          <br>
          <b-button v-on:click="markCurrentMove(2)" variant="success" style="width: 100%; margin-bottom: 4px;">Great</b-button>
        </div>
      </b-col>      
      <b-col cols="7">
        <p style="font-size: 14px; color: #555; margin-bottom: 0;">
          <i v-if="engineStatus === 'analyzing'">Eval: {{currEval}} | best move: {{currBestMove}} | depth: {{currDepth}}</i>
          <i v-else>Engine is being prepared...</i>
        </p>

        <div style="width: 600px; height: 600px;" id="cfc-board"></div>
      </b-col>
      <b-col cols="3">
        <div style="max-height: 600px; overflow: auto; margin-top: 24px;">
          <b-table small hover :items="pgnMoveList" :fields="['white', 'black']">
            <template slot="white" slot-scope="data">
              <a class="pgn_link" v-on:click.prevent="goToMove(data.item.movenum, 'w')">
                <span v-bind:style="moveListMoveStyle(data.item.movenum, 'w')">{{data.item.white}}</span>  
              </a>
            </template>            
            
            <template slot="black" slot-scope="data">
              <a class="pgn_link" v-on:click.prevent="goToMove(data.item.movenum, 'b')">
                <span v-bind:style="moveListMoveStyle(data.item.movenum, 'b')">{{data.item.black}}</span>  
              </a>
            </template>   
                  
          </b-table>
        </div>
      </b-col>      
    </b-row>


  </div>
</template>

<script>

  import _ from 'lodash'
  import {Chessboard} from "cm-chessboard/src/cm-chessboard/Chessboard.js"
  import Chess from 'chess.js';
  
  import API from '@/api'

  //import EnginePool from '@/services/enginepool'
  
  export default {
    name: 'ProcessGame',
    props: ['id'],
    data() {
      return {
        // Some are commented out to avoid Vue reactivity for these
        
        loading: false,
        // Board & its state
        animating: false,
        pendingBoardRefresh: false,
        // board: null,

        pgnMoveList: [],
        currMove: null,

        // Verdicts
        verdicts: [],

        // ChessJs instance
        game: null,
        // chessjs: null
        moveHistory: null,
        currIndexInMoveHistory: -1,

        // Stockfish
        // stockfish: null
        analyzing: false,
        waitingForBestMove: false,
        analyzingOnFen: null,
        currEval: '---',
        currDepth: '---',
        currBestMove: '---',
        engineStatus: '???'

        // keyListener: null
      }
    },
    computed: {
      currentMove: function() {
        if (this.currIndexInMoveHistory >= 0) {
          var m = this.moveHistory[this.currIndexInMoveHistory]

          var halfmove = this.currIndexInMoveHistory+1;

          if (halfmove % 2 !== 0) {
            // Whites move
            return (Math.floor(halfmove/2)+1) + '. ' + m;
          } else {
            return (halfmove / 2) + '... ' + m;
          }
        } 

        return null;
      }
    },
    created() {
      this.loadGame();

      this.stockfish = new Worker("/js/stockfish/stockfish.js");

      this.stockfish.onmessage = function onmessage(event) {
          // console.log(event.data);
          this.messageFromStockfish(event.data);
      }.bind(this);

      this.stockfish.postMessage('ucinewgame');
      this.stockfish.postMessage('isready');

      this.keyListener = document.addEventListener('keyup', (evt) => {
        console.log(evt.keyCode);

        if (evt.keyCode === 37) {
          // Right
          this.showPrevPosition();
          evt.stopPropagation();
        } else if (evt.keyCode === 39) {
          // Left
          this.showNextPosition();
          evt.stopPropagation();
        }
      });      
    },
    beforeDestroy() {
      if (this.keyListener) {
        document.removeEventListener(this.keyListener);
        this.keyListener = null;
      }

      if (this.stockfish) {
        this.stockfish.postMessage('stop');
        this.stockfish = null;
      }
    },
    mounted() {
      this.board = new Chessboard(
        document.getElementById("cfc-board"),
        { 
          position: "start",
          animationDuration: 90,
          sprite: {
              url: "/images/chessboard-sprite.svg", // pieces and markers are stored as svg in the sprite
              grid: 40 // the sprite is tiled with one piece every 40px
          } 
        }
      )

    },
    methods: {
      moveListMoveStyle: function(movenum, color) {

        if (color === 'b') {
          var useBold = this.currIndexInMoveHistory === (movenum-1)*2+1
        } else {
          var useBold = this.currIndexInMoveHistory === (movenum-1)*2;
        }

        var verdict = _.find(this.verdicts, (v) => {
          return v.halfmovenum === (movenum*2-1) + (color === 'b' ? 1 : 0)
        })

        if (!verdict) {
          var color = 'none';
        } else {
          var colors = {
            '-2': 'crimson',
            '-1': 'orange',
            '0': 'yellow',
            '1': '#66ff66',
            '2': '#22ff22',
          }

          var color = colors[verdict.verdict.toString()];
        }

        return {
          'font-weight': useBold ? 'bold' : 'normal',
          'background': color
        }
      },
      markCurrentMove(verdict) {
        // -2, -1, 0, 1, 2

        var currI = this.currIndexInMoveHistory;

        // TODO: use Vuex for verdict tracking.

        // Remove old verdict if such existed
        this.verdicts = _.filter(this.verdicts, (v) => {
          return v.halfmovenum !== (currI+1);
        });

        // Add 
        this.verdicts.push({
          halfmovenum: currI+1,
          verdict: verdict
        })

        console.log(this.verdicts);
      },

      messageFromStockfish(info) {

        // info depth 2 score cp 214 time 1242 nodes 2124 nps 34928 pv e2e4 e7e5 g1f3

        var parts = info.split(' ');
        //console.log(parts);

        var bmI = _.indexOf(parts, 'bestmove');

        if (bmI !== -1) {
          console.warn('Bestmove received from engine');
          this.analyzing = false;
          this.waitingForBestMove = false;
          // Start analyzing current position
          this.analyzePosition(this.chessjs.fen());
          return;
        }

        if (!this.waitingForBestMove) {

          var pvI = _.indexOf(parts, 'pv');
          var dI = _.indexOf(parts, 'depth');
          var scoreI = _.indexOf(parts, 'score');

          if (pvI !== -1 && dI !== -1) {
            var bestMove = parts[pvI+1];
            var d = parts[dI+1];
            //console.log('Best move at depth ' + d + ' is ' + bestMove);
            this.currDepth = d;
            this.currBestMove = bestMove;
            this.updateUiWithBestMove(bestMove, d);
          }

          if (scoreI !== -1) {
            var score = parts[scoreI+2];
            //console.log("Current score is " + score);
            this.currEval = this.isWhiteToMoveInFen(this.analyzingOnFen) ? score : score * (-1);
            this.updateUiWithScore(score);
          }
          
        }

      },
      isWhiteToMoveInFen(fen) {
        return fen.split(' ')[1] === 'w';
      },
      isBlackToMoveInFen(fen) {
        return fen.split(' ')[1] === 'b';
      },
      whoIsToMoveInFen(fen) {
        return fen.split(' ')[1];
      },
      updateUiWithBestMove(bestmove) {
        this.board.removeMarkers();

        var from = bestmove.slice(0,2);
        var to = bestmove.slice(2,4);

        //console.log("Best move is " + from + " -> " + to);

        this.board.addMarker(from);
        this.board.addMarker(to);

      },
      updateUiWithScore() {},

      goToMove(moveNum, color) {

        var wantedIndexInMoveHistory = color === 'w' ? ((moveNum-1) * 2) : ((moveNum-1) * 2) + 1;

        var diff = this.currIndexInMoveHistory - wantedIndexInMoveHistory;

        console.log(
          "Curr moveHistory index: " 
          + this.currIndexInMoveHistory 
          + ", wanted index: " 
          + wantedIndexInMoveHistory
          + ", diff: "
          + diff
        );

        if (diff > 0) {
          for (var i = diff - 1; i >= 0; i--) {
            this.showPrevPosition(true);
          }
        } else if (diff < 0) {
          diff = diff * (-1);
          for (var i = diff - 1; i >= 0; i--) {
            this.showNextPosition(true);
          }
        } else {
          // Already at correct position
          return;
        }

        this.refreshPositionOnBoard();


      },

      showPrevPosition(skipRefresh) {
        if (this.currIndexInMoveHistory >= 0) {

          this.currIndexInMoveHistory--;

          console.log("Move index: " + this.currIndexInMoveHistory);

          this.chessjs.undo();

          if (!skipRefresh) {
            this.refreshPositionOnBoard();
          }
        }
      },
      showNextPosition(skipRefresh) {
        if (this.currIndexInMoveHistory < this.moveHistory.length) {

          this.currIndexInMoveHistory++;

          console.log("Move index: " + this.currIndexInMoveHistory);

          // Get move to play on chessjs instance
          var move = this.moveHistory[this.currIndexInMoveHistory];

          console.log("Playing move " + move);
          this.chessjs.move(move);

          if (!skipRefresh) {
            this.refreshPositionOnBoard();
          }
        }
      },

      analyzePosition(fen) {
        if (this.waitingForBestMove) {
          return;
        }

        if (this.analyzing) {
          // Inform we don't want to analyze old pos anymore
          console.warn("Telling stockfish to stop");
          this.stockfish.postMessage('stop');
          this.waitingForBestMove = true;
          this.engineStatus = 'stopping';
          return;
        }

        console.warn("Set stockfish to fen " + fen);
        
        this.stockfish.postMessage("position fen " + fen);
        this.stockfish.postMessage("go ponder");

        this.analyzingOnFen = fen;
        this.analyzing = true;
        this.engineStatus = 'analyzing';
      },

      refreshPositionOnBoard() {

        if (this.animating) {
          this.pendingBoardRefresh = true;
          return;
        }

        var fen = this.chessjs.fen();

        console.log("Set board to FEN: " + fen);

        this.animating = true;

        this.analyzePosition(fen);
        
        this.board.removeMarkers();

        return this.board.setPosition(fen)
        .then(() => {
          this.animating = false;
          if (this.pendingBoardRefresh) {
            this.pendingBoardRefresh = false;
            this.refreshPositionOnBoard();
          }
        })
      },

      produceMoveListFromHistory(moveHistory) {
        var nthMove = 0;
        var nthHalfMove = 0;
        var moveList = [];

        _.each(moveHistory, (move) => {

          nthHalfMove++;

          if (nthHalfMove % 2 !== 0) {
            // Whites move
            nthMove++;

            moveList.push({
              white: nthMove + '.' + move,
              movenum: nthMove
            });

          } else {
            moveList[moveList.length-1].black = move;
          }

        })

        this.pgnMoveList = moveList;

      },

      loadGame() {
        this.loading = true;

        return API.process.getUnprocessedGame(this.id)
        .then((game) => {
          console.warn(game);
          this.game = game;

          // Get move history using temp chessJs instance
          var temp_chessjs = new Chess();
          temp_chessjs.load_pgn(game.pgn);
          this.moveHistory = temp_chessjs.history();

          console.log(this.moveHistory);

          this.produceMoveListFromHistory(this.moveHistory);

          temp_chessjs = null;

          this.chessjs = new Chess();
          setTimeout(this.refreshPositionOnBoard.bind(this));

          this.loading = false;
        })
      },

    }
  }
</script>

<style scoped>
  .pgn_link {
    cursor: pointer;
  }

  .pgn_link:hover {
    font-weight: bold;
  }
</style>