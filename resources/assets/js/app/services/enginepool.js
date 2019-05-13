const engines = [
	{
		_engine: null,
		analysisStarted: null,
		id: 1,
		busy: false,
		fen: null,
		msgCb: null
	},
	{
		id: 2,
		analysisStarted: null,
		busy: false,
		fen: null,
		msgCb: null
	},
	{
		id: 3,
		analysisStarted: null,
		busy: false,
		fen: null,
		msgCb: null
	},		
];

import _ from 'lodash'

export default {
	
	waitingForAvailability: null,

	init: function() {
		_.each(engines, (engineHolder) => {
			engineHolder._engine = new Worker("/js/stockfish/stockfish.js");
		});
	},
	destroy: function() {
		_.each(engines, (engineHolder) => {
			if (engineHolder._engine) {
				engineHolder._engine.postMessage('stop');
				engineHolder._engine = null;
				engineHolder.msgCb = null;
			}
		});		

	},
	requestStopForAllEngines() {
		_.each(engines, (engineHolder) => {
			if (engineHolder._engine && engineHolder.busy) {
				engineHolder._engine.postMessage('stop');
			}
		});	
	},
	analyze: function(fen, msgCb) {

		this.requestStopForAllEngines();

		var availableEngine = _.find(engines, (engineHolder) => {
			return !engineHolder.busy;
		});

		if (!availableEngine) {
			return false;
		}

		availableEngine.postMessage('ucinewgame');
		availableEngine.msgCb = msgCb;
		availableEngine.fen = fen;
		availableEngine.onmessage = function messageFromStockfish(info) {

	        // info depth 2 score cp 214 time 1242 nodes 2124 nps 34928 pv e2e4 e7e5 g1f3

	        var parts = info.split(' ');
	        //console.log(parts);

	        var bmI = _.indexOf(parts, 'bestmove');

	        if (bmI !== -1) {
	          console.warn('Bestmove received from engine ' + availableEngine.id);
	          availableEngine.busy = false;
	          availableEngine.msgCb = null;
	          availableEngine.onmessage = () => {}; // no-op
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
	            this.currDepth = d;
	            this.currBestMove = bestMove;
	          }

	          if (scoreI !== -1) {
	            var score = parts[scoreI+2];
	            console.log("Current score is " + score);
	            this.currEval = this.isWhiteToMoveInFen(this.analyzingOnFen) ? score : score * (-1);
	          }

	          availableEngine.msgCb({
	          	fen: availableEngine.fen,
	          	evaluation: currEval,
	          	depth: currDepth,
	          	bestmove: currBestMove
	          });
	          
	        }

	      };
		availableEngine.analysisStarted = Date.now();

		availableEngine.postMessage("position fen " + fen);
		availableEngine.postMessage("go ponder");

	}

}