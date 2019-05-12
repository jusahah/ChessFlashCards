import _ from 'lodash'
import axios from 'axios'
import AuthService from '@/auth/auth'
import Promise from 'bluebird'

import apiCall from '../apicall'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  // Api key validation during login flow
  uploadGames(formData) {

    var headers = AuthService.getHeaders();
    headers['Content-Type'] = 'multipart/form-data';

    return apiCall(
      axios.post(urlprefix + '/upload', formData, {
        headers: headers
      })
      .then((response) => {
        return response.data
      })      

    );     
  },

}

