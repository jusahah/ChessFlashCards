<template>
  <div>
    <h1>Process the game {{id}}</h1>

    <div style="width: 600px; height: 600px;" id="cfc-board"></div>


  </div>
</template>

<script>

  import _ from 'lodash'
  import {Chessboard} from "cm-chessboard/src/cm-chessboard/Chessboard.js"
  import Chess from 'chess.js';
  
  import API from '@/api'
  
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

        // ChessJs instance
        game: null,
        // chessjs: null
        moveHistory: null,
        currIndexInMoveHistory: -1,

        // Stockfish
        // stockfish: null
        analyzing: false,
        waitingForBestMove: false,

        // keyListener: null
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
            console.log('Best move at depth ' + d + ' is ' + bestMove);
            this.updateUiWithBestMove(bestMove, d);
          }

          if (scoreI !== -1) {
            var score = parts[scoreI+2];
            console.log("Current score is " + score);
            this.updateUiWithScore(score);
          }
          
        }

      },

      updateUiWithBestMove(bestmove) {
        this.board.removeMarkers();

        var from = bestmove.slice(0,2);
        var to = bestmove.slice(2,4);

        console.log("Best move is " + from + " -> " + to);

        this.board.addMarker(from);
        this.board.addMarker(to);

      },
      updateUiWithScore() {},

      showPrevPosition() {
        if (this.currIndexInMoveHistory >= 0) {

          this.currIndexInMoveHistory--;

          console.log("Move index: " + this.currIndexInMoveHistory);

          this.chessjs.undo();
          this.refreshPositionOnBoard();
        }
      },
      showNextPosition() {
        if (this.currIndexInMoveHistory < this.moveHistory.length) {

          this.currIndexInMoveHistory++;

          console.log("Move index: " + this.currIndexInMoveHistory);

          // Get move to play on chessjs instance
          var move = this.moveHistory[this.currIndexInMoveHistory];

          console.log("Playing move " + move);
          this.chessjs.move(move);
          this.refreshPositionOnBoard();
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
          return;
        }

        console.warn("Set stockfish to fen " + fen);
        
        this.stockfish.postMessage("position fen " + fen);
        this.stockfish.postMessage("go ponder");

        this.analyzing = true;
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

          temp_chessjs = null;

          this.chessjs = new Chess();
          setTimeout(this.refreshPositionOnBoard.bind(this));

          this.loading = false;
        })
      },

    }
  }
</script>