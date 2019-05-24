import _ from 'lodash'
import axios from 'axios'
import AuthService from '@/auth/auth'
import Promise from 'bluebird'

import apiCall from '../apicall'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  getPositions(fens) {
    return apiCall(
      axios.post(urlprefix + '/positions', {
        fens: fens,
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data.data;
      })      

    );        
  },
  enableTraining(fen) {
    return apiCall(
      axios.post(urlprefix + '/positions/enable-training', {
        fen: fen
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data.data;
      })      

    );  
  },
  disableTraining(fen) {
    return apiCall(
      axios.post(urlprefix + '/positions/disable-training', {
        fen: fen
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data.data;
      })      

    ); 
  },
  saveMoveVerdict(verdict, move, san, fen) {
    /*
    return new Promise((resolve) => {

      setTimeout(() => {
        resolve({move: move, fen: fen, verdict: verdict});
      }, 500);
    });
    */

    return apiCall(
      axios.post(urlprefix + '/verdicts', {
        fen: fen,
        san: san,
        verdict: verdict,
        move: move
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data.data;
      })      

    );     
  },  
  /*
  saveSuggestedMove(fromto, move, fen) {

    return apiCall(
      axios.post(urlprefix + '/suggestedmoves', {
        fen: fen,
        move: move,
        fromto: fromto
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data;
      })     

    );     
  },  
  deleteSuggestedMove(move, fen) {

    return apiCall(
      axios.delete(urlprefix + '/positions/' + encodeURIComponent(fen) + '/suggestedmoves/' + encodeURIComponent(move),{
        headers: AuthService.getHeaders()
      })
      .then(() => {
        return true;
      })      

    );     
  },
  */ 
}

