<template>
  <div>
    <h1>Process the game {{id}}</h1>

    <b-row>
      <b-col cols="2">
        <div v-if="nextMove" style="margin-top: 30px; position: relative; height: 600px;">
          <p>Mark {{nextMove}} as: </p>
          <b-button v-on:click="markNextMove('good')" :disabled="waitingBetterMoveFromUser" variant="success" style="width: 100%; margin-bottom: 4px;">Good</b-button>
          <br>
          <b-button v-on:click="markNextMove('bad')" :disabled="waitingBetterMoveFromUser" variant="danger" style="width: 100%; margin-bottom: 4px;">Bad</b-button>
          <br>
          <hr>
          <b-button v-if="waitingBetterMoveFromUser" v-on:click="cancelGiveBetterMove" variant="warning" style="width: 100%; margin-bottom: 4px;">Cancel giving {{inputtedMoveGetsVerdict}} {{inputLineMode ? 'line' : 'move'}}</b-button>
          <br>
          <b-button v-if="waitingBetterMoveFromUser && inputLineMode" variant="primary" style="width: 100%; margin-bottom: 4px;">Save line!</b-button>
          <template v-else-if="!waitingBetterMoveFromUser">
            <b-button v-on:click="giveCorrectMove" variant="secondary" style="width: 100%; margin-bottom: 4px;">Give good move</b-button>
            <b-button v-if="false" v-on:click="giveCorrectMoves" variant="secondary" style="width: 100%; margin-bottom: 4px;">Give good line</b-button>
            <b-button v-on:click="giveIncorrectMove" variant="secondary" style="width: 100%; margin-bottom: 4px;">Give bad move</b-button>
          </template>
          <b-button v-if="positionInTraining" v-on:click="removePositionFromTraining" variant="danger" style="width: 100%; margin-bottom: 4px; position: absolute; bottom: 12px;">Remove position from training!</b-button>
          <b-button v-else v-on:click="addPositionToTraining" :disabled="positiveVerdicts.length === 0" :title="positiveVerdicts.length === 0 ? 'Training requires at least one good move to be added for the position' : 'Add current position on board to training set'" variant="info" style="width: 100%; margin-bottom: 4px; position: absolute; bottom: 12px;">Add position to training!</b-button>
        </div>
      </b-col>      
      <b-col cols="7">
        <p style="font-size: 14px; color: #555; margin-bottom: 0;">
          <i v-if="engineStatus === 'analyzing'">Eval: {{currEval}} | best move: {{currBestMove}} | depth: {{currDepth}}</i>
          <i v-else>Engine is being prepared...</i>
        </p>

        <div style="width: 600px; height: 600px;" id="cfc-board"></div>
        <p v-if="waitingBetterMoveFromUser && inputtedLine.length === 0">Play {{inputtedMoveGetsVerdict}} move on board!</p>
        <p v-else-if="waitingBetterMoveFromUser">
          <span v-for="move in inputtedLine" style="margin-right: 8px;"><i>{{move.san}}</i></span>
        </p>
        <div v-else>
          <br>
          <h6>Good moves</h6>

          <b-badge v-for="verdict in positiveVerdicts" :key="verdict.fen + '_' + verdict.move" variant="success" style="position: relative; width: 80px; height: 22px; font-size: 16px; margin-right: 6px;">
            {{verdict.san}}<span v-on:click="removeVerdict(verdict.id)" style="position: absolute; top: 0; right: 4px; font-size: 20px; font-weight: bold; color: white; cursor: pointer;">x</span>
          </b-badge> 
          <br>
          <h6>Bad moves</h6>

          <b-badge v-for="verdict in negativeVerdicts" :key="verdict.fen + '_' + verdict.move" variant="danger" style="position: relative; width: 80px; height: 22px; font-size: 16px; margin-right: 6px;">
            {{verdict.san}}<span v-on:click="removeVerdict(verdict.id)" style="position: absolute; top: 0; right: 4px; font-size: 20px; font-weight: bold; color: white; cursor: pointer;">x</span>
          </b-badge>   

        </div>  
      </b-col>
      <b-col cols="3">
        <div style="max-height: 600px; overflow: auto; margin-top: 24px;">
          <b-table small hover v-bind:style="pgnListStyle" :items="pgnMoveTable" :fields="['white', 'black']">
            <template slot="white" slot-scope="data">
              <a class="pgn_link" v-on:click.prevent="goToMove(data.item.movenum, 'w')">
                <span v-bind:style="moveTableStyle(data.item.movenum, 'w')">{{data.item.white}}</span>  
              </a>
            </template>            
            
            <template slot="black" slot-scope="data">
              <a class="pgn_link" v-on:click.prevent="goToMove(data.item.movenum, 'b')">
                <span v-bind:style="moveTableStyle(data.item.movenum, 'b')">{{data.item.black}}</span>  
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
  import {Chessboard, MOVE_INPUT_MODE, MARKER_TYPE, INPUT_EVENT_TYPE, COLOR} from "cm-chessboard/src/cm-chessboard/Chessboard.js"
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
        syncing: false,
        // Board & its state
        animating: false,
        pendingBoardRefresh: false,

        waitingBetterMoveFromUser: false,
        inputtedMoveGetsVerdict: null,
        inputLineMode: false,
        inputtedLine: [],
        lineChessJs: null,

        // board: null,

        pgnMoveList: [],
        currMove: null,
        currentFen: null,

        correctMoves: {},
        betterMoves: [],

        positions: [],

        // Verdicts
        /*
          Schema: 
          [
            {move: 'a1a2', fen: '...', verdict: 1},
            ...
          ]
        */
        // verdicts: [],

        // ChessJs instance
        game: null,
        // boardChessjs: null
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
      pgnMoveTable: function() {
        var table = [];
        var nth = 1;

        this.pgnMoveList.forEach(function(move) {
          if (nth % 2 !== 0) {
            table.push({
              white: move.text,
              black: null,
              movenum: Math.floor(nth / 2)+1
            })
          } else {
            table[table.length-1].black = move.san;
          }

          nth++;
        });

        // console.warn(table)

        return table;
      },
      pgnListStyle: function() {
        return {color: this.waitingBetterMoveFromUser ? '#999' : '#222'};
      },
      nextMove: function() {
        if (this.currIndexInMoveHistory >= -1) {
          if (this.moveHistory && this.currIndexInMoveHistory < this.moveHistory.length-1) {
            var m = this.moveHistory[this.currIndexInMoveHistory+1]

            console.warn(this.currIndexInMoveHistory)
            var halfmove = this.currIndexInMoveHistory+1;

            if (halfmove % 2 !== 0) {
              // Whites move
              return (Math.floor(halfmove/2)+1) + '... ' + m.san;
            } else {
              return ((halfmove / 2)+1) + '. ' + m.san;
            }
          }
        } 

        return null;
      },
      positionInTraining: function() {
        if (this.positions && this.currentFen) {

          var position = _.find(this.positions, (p) => {
            return p.fen === this.currentFen;
          });

          if (position) {
            return !!position.trainable;
          }
        }        

        return false;
      },
      positiveVerdicts: function() {

        if (this.positions && this.currentFen) {

          var position = _.find(this.positions, (p) => {
            return p.fen === this.currentFen;
          });

          if (position) {
            return _.filter(position.verdicts, (v) => {
              return v.verdict === 'good' || v.verdict === 'great';
            });
          }
        }

        return [];


      },
      negativeVerdicts: function() {

        if (this.positions && this.currentFen) {

          var position = _.find(this.positions, (p) => {
            return p.fen === this.currentFen;
          });

          if (position) {
            return _.filter(position.verdicts, (v) => {
              return v.verdict === 'bad' || v.verdict === 'terrible';
            });
          }
        }

        return [];


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
          moveInputMode: MOVE_INPUT_MODE.dragPiece,
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
      removePositionFromTraining() {
        console.log("Removing fen " + this.currentFen + " to training set");
        return API.position.disableTraining(this.currentFen)
        .then((position) => {
          this.positions = _.map(this.positions, (p) => {
            if (p.id === position.id) {
              return position;
            }

            return p;
          });
        }) 
      },
      addPositionToTraining() {
        console.log("Adding fen " + this.currentFen + " to training set");
        return API.position.enableTraining(this.currentFen)
        .then((position) => {
          this.positions = _.map(this.positions, (p) => {
            if (p.id === position.id) {
              return position;
            }

            return p;
          });
        })        
      },
      removeVerdict(verdictId) {
        return API.verdict.removeVerdict(verdictId)
        .then(() => {
          // Remove from positions
          _.each(this.positions, (p) => {
            if (p.verdicts && verdictId) {
              p.verdicts = _.filter(p.verdicts, (v) => {
                return v.id !== verdictId;
              })
            }
          });          
        })
      },
      addMoveAsCorrectOrIncorrectOne(move, fen) {
        if (!this.inputtedMoveGetsVerdict) {
          console.warn("addMoveAsCorrectOrIncorrectOne does not know what verdict is");
          return;
        }

        console.log("Move marked as correct/incorrect one");
        console.log(move);
        console.log(fen);

        var fromto = move.from + move.to;
        var san = move.san;

        var verdict = this.inputtedMoveGetsVerdict; 

        this.inputtedMoveGetsVerdict = null;

        return API.position.saveMoveVerdict(verdict, fromto, san, fen)
        .then((position) => {
          console.error(position);
          //var move = position.move; // 'a1a2'
          //var verdict = position.verdict;
          var fen = position.fen;

          var found = false;

          this.positions = _.map(this.positions, (p) => {
            if (p.fen === position.fen) {
              found = true;
              return position; // Replace
            }

            return p;
          });

          if (!found) {
            this.positions.push(position);
          }

          //console.warn("New positions");
          //console.warn(this.positions);

          //throw new Error('Skip');

          /*
          var pos = _.find(this.positions, (pos) => {
            return pos.fen === fen;
          })

          if (pos) {

            // Remove old verdict if such existed
            pos.verdicts = _.filter(pos.verdicts, (v) => {
              return v.move !== move || v.fen !== fen;
            });

            // Add 
            pos.verdicts.push({
              fen: fen,
              move: move,
              verdict: verdict
            })

          }
          */


          this.syncing = false;

          this.refreshPositionOnBoard();

        })        

        // this.correctMoves[fen] = move;
      },
      moveInputHandler(event) {
        if (event.type === INPUT_EVENT_TYPE.moveDone) {

          if (this.inputLineMode) {

            console.log("Move done!!");
            const move = {from: event.squareFrom, to: event.squareTo}
            var fenBeforeMove = this.lineChessJs.fen();

            var res = this.lineChessJs.move(move);

            if (res) {
              event.chessboard.disableMoveInput();
              this.inputtedLine.push(res);
              console.warn(this.inputtedLine);
              // Set give mode again.
              this.setGiveMoveMode();
            }

            this.refreshPositionOnBoard();

            return true;
          } else {
            console.log("Move done!!");
            const move = {from: event.squareFrom, to: event.squareTo}
            var fenBeforeMove = this.boardChessjs.fen();

            var res = this.boardChessjs.move(move);

            if (res) {
              event.chessboard.disableMoveInput();
              this.addMoveAsCorrectOrIncorrectOne(res, fenBeforeMove);
              this.waitingBetterMoveFromUser = false;
              this.boardChessjs.undo();
            }

            this.refreshPositionOnBoard();
            /*
            if (this.whoIsToMoveInFen(this.boardChessjs.fen()) === 'w') {
              event.chessboard.enableMoveInput(this.moveInputHandler.bind(this), COLOR.white)
            } else {
              event.chessboard.enableMoveInput(this.moveInputHandler.bind(this), COLOR.black)
            }
            */
            return true;
          }
        } 

        return true;

      },
      moveTableStyle: function(movenum, color) {

        if (color === 'b') {
          var moveIndex = (movenum-1)*2+1;
        } else {
          var moveIndex = (movenum-1)*2;
        }

        var useBold = this.currIndexInMoveHistory === moveIndex;

        var pgnMoveListMove = this.pgnMoveList[moveIndex];

        if (!pgnMoveListMove) {
          return {};
        }

        //console.warn(movenum + ' for ' + color);
        //console.warn(pgnMoveListMove);
        
        var verdict = null;

        _.each(this.positions, (pos) => {

          if (verdict) return; // Return early, already found

          if (pos.fen === pgnMoveListMove.fen) {
            // Search for this position's verdicts for match

            return _.each(pos.verdicts, (v) => {

              if (v.move === pgnMoveListMove.fromto) {
                verdict = v;
              }

            })
          }

          //return pgnMoveListMove.fen === v.fen && pgnMoveListMove.fromto === v.move;

          //return v.halfmovenum === (movenum*2-1) + (color === 'b' ? 1 : 0)

        })

        // console.warn("found verdict: " + !!verdict)

        if (!verdict) {
          var color = 'none';
        } else {
          var colors = {
            'terrible': 'crimson',
            'bad': 'orange',
            'neutral': 'yellow',
            'good': '#66ff66',
            'great': '#22ff22',
          }

          var color = colors[verdict.verdict.toString()];
        }

        return {
          'font-weight': useBold ? 'bold' : 'normal',
          'background': color
        }
      },
      giveCorrectMove() {
        //this.showPrevPosition();
        this.inputtedMoveGetsVerdict = 'good';
        this.inputLineMode = false;
        this.setGiveMoveMode();
      },
      giveCorrectMoves() {
        
        this.lineChessJs = new Chess();
        var s = this.lineChessJs.load(this.boardChessjs.fen());
        
        if (!s) {
          console.error('Could not load FEN into temporary chessjs instance for sideline inputting');
          this.lineChessJs = null;
          return false;
        }

        this.boardChessjs = this.lineChessJs;

        //this.showPrevPosition();
        this.inputtedMoveGetsVerdict = 'good';
        this.inputLineMode = true;
        this.inputtedLine = [];
        this.setGiveMoveMode();
      },      
      giveIncorrectMove() {
        //this.showPrevPosition();
        this.inputtedMoveGetsVerdict = 'bad';
        this.inputLineMode = false;
        this.setGiveMoveMode();
      },      
      setGiveMoveMode() {
        this.waitingBetterMoveFromUser = true;
        var color = this.whoIsToMoveInFen(this.boardChessjs.fen()) === 'w' ? COLOR.white : COLOR.black; 
        this.board.enableMoveInput(this.moveInputHandler.bind(this), color)
      },
      cancelGiveBetterMove() {
        this.lineChessJs = null;
        this.boardChessjs = this.gameChessJs; // Set original back to business.
        this.inputLineMode = false;
        this.inputtedLine = [];
        this.waitingBetterMoveFromUser = false;
        //this.showNextPosition();

        this.refreshPositionOnBoard();
      },

      getMoveFromMoveHistory(index) {
        return _.clone(this.moveHistory[index]);
      },

      markNextMove(verdict) {
        // -2, -1, 0, 1, 2

        var currI = this.currIndexInMoveHistory+1;

        // TODO: use Vuex for verdict tracking.

        this.syncing = true;

        var move = this.getMoveFromMoveHistory(currI);
        var fromto = move.from + move.to;
        var san = move.san;

        console.log("Marking move " + fromto + " as " + verdict);

        return API.position.saveMoveVerdict(verdict, fromto, san, this.boardChessjs.fen())
        .then((position) => {
          console.error(position);
          var move = position.move; // 'a1a2'
          var verdict = position.verdict;
          var fen = position.fen;

          var found = false;

          this.positions = _.map(this.positions, (p) => {
            if (p.fen === position.fen) {
              found = true;
              return position; // Replace
            }

            return p;
          });

          if (!found) {
            this.positions.push(position);
          }

          console.warn("New positions");
          console.warn(this.positions);

          //throw new Error('Skip');

          /*
          var pos = _.find(this.positions, (pos) => {
            return pos.fen === fen;
          })

          if (pos) {

            // Remove old verdict if such existed
            pos.verdicts = _.filter(pos.verdicts, (v) => {
              return v.move !== move || v.fen !== fen;
            });

            // Add 
            pos.verdicts.push({
              fen: fen,
              move: move,
              verdict: verdict
            })

          }
          */


          this.syncing = false;

        })


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
          setTimeout(() => {
            if (this.boardChessjs) {
              this.analyzePosition(this.boardChessjs.fen());
            }
          }, 1000);

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
        //this.board.removeMarkers();

        var from = bestmove.slice(0,2);
        var to = bestmove.slice(2,4);

        //console.log("Best move is " + from + " -> " + to);

        //this.board.addMarker(from);
        //this.board.addMarker(to);

      },
      updateUiWithScore() {},

      goToMove(moveNum, color) {



        if (this.waitingBetterMoveFromUser) {
          return;
        }

        

        var wantedIndexInMoveHistory = color === 'w' ? ((moveNum-1) * 2) : ((moveNum-1) * 2) + 1;

        var diff = this.currIndexInMoveHistory - wantedIndexInMoveHistory;

        console.log(
          "Go to movenum: " + moveNum + ", curr moveHistory index: " 
          + this.currIndexInMoveHistory 
          + ", wanted index: " 
          + wantedIndexInMoveHistory
          + ", diff: "
          + diff
        );

        if (diff > 0) {
          this.board.removeMarkers();
          for (var i = diff - 1; i >= 0; i--) {
            this.showPrevPosition(true);
          }
        } else if (diff < 0) {
          this.board.removeMarkers();
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

        if (this.waitingBetterMoveFromUser) {
          return;
        }

        if (this.currIndexInMoveHistory >= 0) {

          this.currIndexInMoveHistory--;

          //this.board.removeMarkers();

          this.boardChessjs.undo();

          if (!skipRefresh) {
            this.refreshPositionOnBoard();
          }
        }
      },
      showNextPosition(skipRefresh) {

        if (this.waitingBetterMoveFromUser) {
          return;
        }

        if (this.currIndexInMoveHistory < this.moveHistory.length-1) {

          this.currIndexInMoveHistory++;

          // Get move to play on boardChessjs instance
          var move = this.moveHistory[this.currIndexInMoveHistory];

          if (!skipRefresh) {
            //this.board.removeMarkers();
            //this.board.addMarker(move.from, MARKER_TYPE.move)
            //this.board.addMarker(move.to, MARKER_TYPE.move)
            
          }

          this.boardChessjs.move(move);

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
      /*
      refreshBetterMoves(fen) {
        var pos = _.find(this.positions, (pos) => {
          return pos.fen === fen;
        });

        if (pos) {
          console.warn("Better moves from pos");
          console.warn(pos);
          this.betterMoves = pos.bettermoves;
        } else {
          this.betterMoves = [];
        }
      },
      */  

      refreshPositionOnBoard() {

        if (this.animating) {
          this.pendingBoardRefresh = true;
          return;
        }


        this.board.removeMarkers();

        var fen = this.boardChessjs.fen();
        this.currentFen = fen;
        //this.refreshBetterMoves(fen);

        // Actual played move markers
        setTimeout(() => {
          if (this.currIndexInMoveHistory >= -1 && this.currIndexInMoveHistory < this.moveHistory.length-1) {  

            console.log("Add next move marker");

            var nextMove = this.moveHistory[this.currIndexInMoveHistory+1];
            console.log(nextMove);
            this.board.addMarker(nextMove.from, MARKER_TYPE.move);
            this.board.addMarker(nextMove.to, MARKER_TYPE.move);


          }

        }, 0);

        // Correct move markers
        if (this.correctMoves[fen]) {
          var correctMove = this.correctMoves[fen];
          this.board.addMarker(correctMove.from);
          this.board.addMarker(correctMove.to);
        }

        console.log("Set board to FEN: " + fen);

        this.animating = true;

        //this.analyzePosition(fen);
        
        //this.board.removeMarkers();

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

        var temp_chessjs = new Chess();

        var prevFen = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';

        _.each(moveHistory, (move) => {

          var m = temp_chessjs.move(move);

          if (!m) {
            throw new Error('Can not generate move history! Move ' + move.san + ' failed');
          }

          nthHalfMove++;

          var toMove = nthHalfMove % 2 !== 0 ? 'w' : 'b';

          moveList.push({
            fen: prevFen,
            text: (nthMove+1) + (toMove === 'w' ? '.' : '...') + move.san,
            san: move.san,
            fromto: move.from + move.to,
            color: toMove,
            movenum: nthMove,
            halfmovenum: nthHalfMove
          });

          prevFen = temp_chessjs.fen();

          if (nthHalfMove % 2 === 0) {
            // Whites move
            nthMove++;


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
          this.moveHistory = temp_chessjs.history({verbose: true});

          console.log(this.moveHistory);

          return this.produceMoveListFromHistory(this.moveHistory);
        })
        .then(() => {

          var fens = this.pgnMoveList.map(function(moveListMove) {
            return moveListMove.fen;
          });
          console.log("Load positions for fens");
          console.log(fens);

          return API.position.getPositions(fens);

        })
        .then((positions) => {

          this.positions = positions;
          
          console.warn("Positions for this game");
          console.warn(positions);

          this.gameChessJs = new Chess();
          this.boardChessjs = this.gameChessJs; // Start using this instance as board controlling one.
          setTimeout(this.refreshPositionOnBoard.bind(this));
          

          //this.loading = false;
          return;
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