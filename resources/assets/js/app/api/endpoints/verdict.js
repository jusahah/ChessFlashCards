import _ from 'lodash'
import axios from 'axios'
import AuthService from '@/auth/auth'
import Promise from 'bluebird'

import apiCall from '../apicall'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  getBetterMoves(fens) {
    return [];
  },
  getVerdicts(fens) {
    /*
    return new Promise((resolve) => {

      setTimeout(() => {
        resolve({move: move, fen: fen, verdict: verdict});
      }, 500);
    });
    */

    return apiCall(
      axios.post(urlprefix + '/moveverdicts/get', {
        fens: fens,
      },{
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data;
      })      

    );     
  },  
}

