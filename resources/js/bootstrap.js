// import _ from 'lodash';
// window._ = _;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */
import axios from 'axios';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['X-CSRF-TOKEN'] = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content');
axios.defaults.withCredentials = true;
/*
axios.defaults.onUploadProgress = progressEvent => {
  const totalLength = progressEvent.lengthComputable
    ? progressEvent.total
    : progressEvent.target.getResponseHeader('content-length') ||
      progressEvent.target.getResponseHeader('x-decompressed-content-length');
  if (totalLength !== null) {
    store.dispatch(
      'setProgress',
      Math.round((progressEvent.loaded * 100) / totalLength)
    );
  }
};
axios.defaults.onDownloadProgress = progressEvent => {
  const totalLength = progressEvent.lengthComputable
    ? progressEvent.total
    : progressEvent.target.getResponseHeader('content-length') ||
      progressEvent.target.getResponseHeader('x-decompressed-content-length');
  if (totalLength !== null) {
    store.dispatch(
      'setProgress',
      Math.round((progressEvent.loaded * 100) / totalLength)
    );
  }
};
axios.interceptors.request.use(config => {
  store.dispatch('setLoading', true);
  return config;
});
axios.interceptors.response.use(config => {
  store.dispatch('setLoading', false);
  return config;
});
*/
window.axios = axios;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });

export { axios };
