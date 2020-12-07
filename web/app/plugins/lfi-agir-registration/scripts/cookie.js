(function () {

  if ( typeof window.CustomEvent === "function" ) return false;

  function CustomEvent ( event, params ) {
    params = params || { bubbles: false, cancelable: false, detail: null };
    var evt = document.createEvent( 'CustomEvent' );
    evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
    return evt;
  }

  window.CustomEvent = CustomEvent;
})();

window.addEventListener('DOMContentLoaded', function () {
  var i, pair;

  var hashParams = new URLSearchParams(location.hash.replace("#", "?"));

  for (pair of hashParams.entries()) {
    if (pair[0].startsWith("agir_")) {
      Cookies.set(pair[0], pair[1], {sameSite: 'strict'});
    }
  }

  var queryParams = new URLSearchParams(location.search);

  var readParams = [];
  for (pair of queryParams.entries()) {
    if (pair[0].startsWith("agir_")) {
      Cookies.set(pair[0], pair[1], {sameSite: 'strict'});
      readParams.push(pair[0]);
    }
  }

  for (i = 0; i < readParams.length; i++) {
    queryParams.delete(readParams[i]);
  }

  var paramString = queryParams.toString() !== "" ? ("?" + params.toString()) : '';
  window.history.replaceState(null, null, window.location.pathname + paramString);

  var cookies = Cookies.get();
  var elements;

  for (var name in cookies) {
    if(!cookies.hasOwnProperty( name ) || !name.startsWith("agir_")) {
      continue;
    }

    var selector = '[data-agir-cookie="' + name.replace("agir_", '') + '"]';
    elements = document.querySelectorAll(selector);
    for (i = 0; i < elements.length; i++) {
      elements[i].innerText= cookies[name];
    }

    var inputSelector = 'input[name="form_fields[' + name + ']"]';
    elements = document.querySelectorAll(inputSelector);
    for (i = 0; i < elements.length; i++) {
      elements[i].value = cookies[name];
    }
  }

  var event = new CustomEvent('agirCookiesLoaded');
  window.dispatchEvent(event);
});