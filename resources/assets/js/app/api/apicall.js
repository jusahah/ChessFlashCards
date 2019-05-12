import _ from 'lodash'
import Promise from 'bluebird'
import router from '@/routes/router'
import EventBus from '@/services/eventbus';

const HTTP_DELAY = process.env.NODE_ENV === 'slow-development' ? 500 : 0;

// This provides our own wrapper for HTTP requests.
export default function wrap(prom) {

  // This promise will be returned to the original caller of API method.
  var wrapperPromise = Promise.resolve();

  return wrapperPromise.delay(HTTP_DELAY).then(function() {
    // Catch errors coming from network (prom === axios promise object)
    return prom.catch(function(error) {
        /*
        // TODO: Catch errors and notify 

        if (error.response.status === 500) {
          // General usecase failure
          EventBus.$emit('api-call-fail', error.message);
        } else if (error.response.status === 501) {
          // Not implemented yet
          EventBus.$emit('api-call-fail', 'Palvelu ei saatavilla toistaiseksi.');
        } else if (error.response.status === 402) {
          // Subscription upgrade required
          EventBus.$emit('api-call-subscription-fail');
        } else if (error.response.status === 403) {
          // Authorization fail
          EventBus.$emit('api-call-fail', 'Käyttäjäoikeutesi eivät riitä!');
        } else if (error.response.status === 422) {
          // Validation fail
          EventBus.$emit('api-call-fail', 'Lomaketta ei voitu käsitellä. Tarkista syöttämäsi tietue.');
        } else if (error.response.status === 401) {
          EventBus.$emit('api-call-fail', 'Sessio päättynyt. Kirjaudu sisään.');
          EventBus.$emit('force-logout');
          
        }
        */

        if (error.response.status === 401) {
          // Authentication error -> possible reason is that api key has expired.
          // Whatever the reason, we must force login flow again.
          EventBus.$emit('show-error', 'Sessio päättynyt. Kirjaudu sisään.');
          router.replace({name: 'logout'});

        } else if (error.response.status === 409 || error.response.status === 422) {
          // Input validation error
          // Check if response has 'errors' property. If yes, we might want to 
          // tell user what it contains.

          var responseData = error.response.data;

          if (responseData.hasOwnProperty('errors')) {
            // Okay, just print its contents to user. He can sort this one out.

            if (typeof responseData.errors === 'object') {
              var errorMsgs = _.values(responseData.errors);
              if (errorMsgs && errorMsgs.length) {
                var errorString = _.join(errorMsgs, ' ');
              } else {
                var errorString = '(No error information found)';
              }
            } else {
              var errorString = responseData.errors;
            }

            // Broadcast for user to see (this will be caught in Member.vue)
            EventBus.$emit('show-error', errorString);

          } else if (responseData.hasOwnProperty('error')) {
            EventBus.$emit('show-error', responseData['error']);
          }

        }
        // Rethrow for the original caller (often a Vue component)
        throw error;

      })

  });

  

}