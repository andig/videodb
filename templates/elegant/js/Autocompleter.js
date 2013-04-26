/**
 * Enhanced Ajax.Autocompleter with support for parameters array and custom completion code
 */
Ajax.Autocompleter2 = Class.create(Autocompleter.Base, {
  initialize: function(element, update, url, options) {
    this.baseInitialize(element, update, options);
    this.options.asynchronous  = true;
    this.options.onComplete    = this.onComplete.bind(this);
    this.options.defaultParams = this.options.parameters || null;
    this.url                   = url;
  },

  // allow parameters array
  getUpdatedChoices: function() {
    this.startIndicator();

    var entry = encodeURIComponent(this.options.paramName) + '=' +
      encodeURIComponent(this.getToken());

    this.options.parameters = this.options.callback ?
      this.options.callback(this.element, entry) : entry;

    if(this.options.defaultParams) {
      // if defaultParams is a string, keep as is and don't convert
      var defaultParams = Object.isString(this.options.defaultParams) ? this.options.defaultParams : Object.toQueryString(this.options.defaultParams);

      this.options.parameters += '&' + defaultParams;
    }

    new Ajax.Request(this.url, this.options);
  },

  // allow custom completion code
  onComplete: function(request) {
    if (this.options.updateContent) {
      this.options.updateContent(request);
      this.stopIndicator();
    }
    else this.updateChoices(request.responseText);
  }
});

