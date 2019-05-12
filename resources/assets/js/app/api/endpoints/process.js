import _ from 'lodash'
import axios from 'axios'
import AuthService from '@/auth/auth'
import Promise from 'bluebird'

import apiCall from '../apicall'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  testConnection() {

    return apiCall(
      axios.get(urlprefix + '/guest-test')
      .then((response) => {
        return response.data
      })      

    );  
  },
  getUnprocessedGame(id) {

    return apiCall(
      axios.get(urlprefix + '/unprocessed-games/' + id, {
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data
      })      

    );     
  },  
  getUnprocessedGames() {

    return apiCall(
      axios.get(urlprefix + '/unprocessed-games', {
        headers: AuthService.getHeaders()
      })
      .then((response) => {
        return response.data
      })      

    );     
  },
  rejectGame(id) {

    return apiCall(
      axios.delete(urlprefix + '/unprocessed-games/' + id, {
        headers: AuthService.getHeaders()
      })
      .then(() => {
        return true;
      })      

    );     
  },  

}

