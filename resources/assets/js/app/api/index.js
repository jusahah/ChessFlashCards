/*
  Wraps Laravel API and HTTP logic behind simpler facade
*/
import _ from 'lodash'
import axios from 'axios'

import AuthService from '../auth/auth'
import apiCall from './apicall'

import authApi from './endpoints/auth'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  auth: authApi
}