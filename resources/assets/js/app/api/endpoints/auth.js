import _ from 'lodash'
import axios from 'axios'
import AuthService from '@/auth/auth'
import Promise from 'bluebird'

import apiCall from '../apicall'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  // Api key validation during login flow
  validateApiKey(apiKey) {
    return apiCall(
      axios.post(urlprefix + '/validatekey', {}, {
        // Include auth headers
        headers: AuthService.getHeaders(apiKey)
      })
      .then((response) => {
        return response.data
      })      

    );     
  },

  getAuthLoginRedirect() {
    return apiCall(
      axios.post(urlprefix + '/oauth/google', {}, {
      })
      .then((response) => {
        return response.data
      })      

    );      
  },

  logout(apiKey) {

    if (apiKey) {
      return apiCall(
        axios.post(urlprefix + '/logout', {}, {
          // Include auth headers
          headers: AuthService.getHeaders(apiKey)        
        })
        .then((_response) => {
          console.log("Logged out from server");
        })
        .catch((e) => {
          // Do nothing
        })      

      );    
      
    } else {
      // No point doing request
      return Promise.resolve();
    }
  }
}

