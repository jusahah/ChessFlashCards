/*
  Wraps Laravel API and HTTP logic behind simpler facade
*/
import _ from 'lodash'
import axios from 'axios'

import AuthService from '../auth/auth'
import apiCall from './apicall'

import authApi from './endpoints/auth'
import uploadApi from './endpoints/upload'
import processApi from './endpoints/process'
import positionApi from './endpoints/position'
import verdictApi from './endpoints/verdict'

// TODO: Move to config/env
const urlprefix = process.env.MIX_LARAVEL_API_PREFIX || 'err-laravel-api-missing';

export default {
  auth: authApi,
  upload: uploadApi,
  process: processApi,
  position: positionApi,
  verdict: verdictApi
}