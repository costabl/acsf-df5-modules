(function ($) {

  $(document).ready(function () {

    const MESSAGE_ACTION_TOGGLE = 'WIDGET_TOGGLE';
    const MESSAGE_ACTION_SIGNUP = 'WIDGET_SIGNUP';
    const MESSAGE_ACTION_SIGNIN = 'WIDGET_SIGNIN';

    const request = (url, data) => {
      return new Promise((resolve, reject) => {
        jQuery.ajax({
          url,
          type: 'POST',
        }).done(data => resolve(data))
          .fail(err => reject(err));
      })
    }

    const requestUpdateAccountState = (url, accountId, state) => {
      return request(url + 'admin/userway/account?accountId=' + accountId + '&state=' + state, {
        accountId,
        state
      })
    };

    const isPostMessageSignAction = (postMessage) => {
      return postMessage.data !== undefined
        && postMessage.data.action
        && postMessage.data.action === MESSAGE_ACTION_SIGNIN
    }

    const isPostMessageAccountToggleAction = (postMessage) => {
      return postMessage.data !== undefined
        && postMessage.data.action
        && postMessage.data.action === MESSAGE_ACTION_TOGGLE
    }

    const isPostMessageSignupAction = (postMessage) => {
      return postMessage.data !== undefined
        && postMessage.data.action
        && postMessage.data.action === MESSAGE_ACTION_SIGNUP
    }

    const prepareUrl = (url)  => {
      let preparedUrl = url;
      if (url.substr(-1) === '/') {
        preparedUrl = url.substr(0, url.length - 1);
      }

      preparedUrl = url.replace(/^http$/g, window.location.protocol.replace(/:/g,''))
      return preparedUrl;
    }

    const selector = document.getElementById('userway-frame');
    const frameContentWindow = selector.contentWindow;

    const {url} = selector.dataset;

    window.addEventListener('message', postMessage => {
      if (!postMessage || postMessage.source !== frameContentWindow) {
        console.log('postMessage -> missed some data', postMessage);
        return;
      }

      const requestUrl = decodeURIComponent(prepareUrl(url));

      console.log('postMessage -> ', postMessage);
      console.log('postMessage data -> ', postMessage.data);
      console.log('postMessage isPostMessageSignupAction -> ', isPostMessageSignupAction(postMessage));
      console.log('postMessage isPostMessageAccountToggleAction -> ', isPostMessageAccountToggleAction(postMessage));
      console.log('postMessage isPostMessageSignAction -> ', isPostMessageSignAction(postMessage));

      if (isPostMessageAccountToggleAction(postMessage)) {
        const state = postMessage.data.state ? 1 : 0;
        const account = postMessage.data?.account;

        if (!account) {
          console.error('[userway] account not found');
          return
        }

        requestUpdateAccountState(requestUrl, account, state)
          .then(res => console.log(res))
          .catch(err => console.error(err));

      } else if (isPostMessageSignupAction(postMessage) || isPostMessageSignAction(postMessage)) {
        const state = postMessage.data.state ? 1 : 0;
        const account = postMessage.data.account;

        if (!account) {
          console.error('[userway] account not found');
          return
        }

        requestUpdateAccountState(requestUrl, account, state)
          .then(res => console.log(res))
          .catch(err => console.error(err));
      }
    });
  });
})(jQuery);

