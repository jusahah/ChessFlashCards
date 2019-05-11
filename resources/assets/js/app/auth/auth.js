import Promise from 'bluebird'
import API from '@/api'

export default {

    // Cached Api key
    apiKey: null,
    userId: null,
    userName: null,
    role: null, // 'admin' or 'user'



    //////////////////////////////////////////////////
    /////////////////////// HEADERS //////////////////
    //////////////////////////////////////////////////
    /*
        These are used in every HTTP request needing authentication.
    */
    getHeaders(apiKey) {
        // Allows overriding by caller
        apiKey = apiKey || this.apiKey;

        if (!apiKey && window.localStorage && window.localStorage.token) {
            this.apiKey = window.localStorage.token;
            apiKey = this.apiKey;
        }

        return {
            'Authorization': 'Bearer ' + apiKey,
            'Accept': 'application/json'
        }
    },
    setUserId(userId) {
        this.userId = userId;
    },
    getUserId() {
        return this.userId;
    },
    getUserName() {
        return this.userName;
    },
    getRole() {
        return this.role;
    },
    setUserName(username) {
        this.userName = username;
    },
    setUserRole(role) {
        this.role = role;
    },    
    setApiKey(apiKey) {
        this.apiKey = apiKey;

        // TODO: Push to Local storage (not implemented for now)

        if (window.localStorage) {
            // Important to not allow XSS to happen with this in localstorage.
            window.localStorage.token = apiKey;
        }
    },
    getApiKey() {
        if (this.apiKey) {
            return this.apiKey;
        }

        if (window.localStorage && window.localStorage.token) {
            return window.localStorage.token;
        }

        return null;

    },
    hasApiKey() {
        if (this.apiKey) {
            return true;
        }

        if (window.localStorage && window.localStorage.token) {
            return true;
        }

        return false;

    },
    logout() {
        var apiKey = this.apiKey;

        // Forget local copy of apikey
        this.apiKey = null;
        this.userId = null;

        if (window.localStorage) {
            window.localStorage.token = '';
        }

        // Send req to server to delete server's copy of api key
        // We don't need to wait for this to finish
        API.auth.logout(apiKey);

        // TODO: Delete api key from local storage (not needed for now)
    },

    // Access control within UI

    isAllowedToManageUsers() {
        // Only admins can do this
        return this.role === 'admin'; 
    },

    isAllowedToManageTasks() {
        // Only admins can do this
        return this.role === 'admin'; 
    }
}