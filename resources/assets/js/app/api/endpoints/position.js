import _ from 'lodash'
import axios from 'axios'
import AuthService from '@/auth/auth'
import Promise from 'bluebird'

import apiCall from '../apicall'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  saveMoveVerdict(verdict, move, fen) {
    /*
    return new Promise((resolve) => {

      setTimeout(() => {
        resolve({move: move, fen: fen, verdict: verdict});
      }, 500);
    });
    */

    return apiCall(
      axios.post(urlprefix + '/moveverdicts', {
        fen: fen,
        verdict: verdict,
        move: move
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data;
      })      

    );     
  },  
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
}

