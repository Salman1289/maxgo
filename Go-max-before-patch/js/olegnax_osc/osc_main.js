var OnestepcheckoutMain = {
    initialize: function() {       
        Ajax.Responders.register({
            onComplete: function(response) {
                if (response.transport.status === 403) {
                    document.location.reload();
                }
            }
        });
    },    
    
    internalRunRequest: function(requestUrl, settings) {
        var requestIOptions = settings || {};        
        var ajaxRequestOptions = Object.extend({}, requestIOptions);
        var classThis = this;
        ajaxRequestOptions = Object.extend(ajaxRequestOptions, 
        {
            onComplete: function(transport)
            {                
                try  { eval("var response = " +  transport.responseText); } 
                catch(e) { var response = { mainBlocks: {} }; }
                var complateAction = classThis.actionUrl(transport.request.url);
                classThis.removeActionFromQueue(complateAction, response);
                classThis.updaterRequest = null;
                if (classThis.updaterQueue.length > 0) 
                { 
                    var founded = [];
                    var toErase = [];
                    classThis.updaterQueue.reverse().each(function(args, key)
                    {
                        var url = args[0];
                        var action = classThis.actionUrl(url);
                        if (founded.indexOf(action) === -1) 
                        {
                            founded.push(action);
                        }
                        else 
                        {
                            toErase.push(key);
                        }
                    });
                    var newQueue = [];
                    classThis.updaterQueue.each(function(args, key)
                    {
                        if (toErase.indexOf(key) === -1) 
                        {
                            newQueue.push(args);
                        } 
                        else 
                        {
                            var action = classThis.actionUrl(args[0]);
                            classThis.removeActionFromQueue(action);
                        }
                    });
                    classThis.updaterQueue = newQueue.reverse();                    
                    var args = classThis.updaterQueue.shift();
                    classThis.internalRunRequest(args[0], args[1]);
                }                 
                if (requestIOptions.onComplete) 
                {
                    requestIOptions.onComplete(transport);
                }
            }
        });        
        this.updaterRequest = new Ajax.Request(requestUrl, ajaxRequestOptions);
    },    
    
    loadingAnimationOFF: function(targetBlock, configuration)
    {
        Object.values(configuration).each(function(classes)
        {            
            targetBlock.select("." + classes.split(" ").join(".")).each(function(element)
            {
                element.remove();
            });
        });
    },    
    
    messageDisplay: function(message, cssClass, targetBlock)
    {
        var classThis = this;
        if (("length" in message) && (typeof(message) === "object")) 
        {
            message.each(function(item)
            {
                classThis.setBlockMessage(item, cssClass, targetBlock);
            });
        } 
        else if(typeof(message) === "string") 
        {
            this.setBlockMessage(message, cssClass, targetBlock);
        }
    },    
    
    setConfiguration: function(configuration) 
    {
        this.updaterRequest = null;
        this.updaterQueue = [];
        this.mainQueue = {};
        this.mainBlocks = {};

        var classThis = this;
        if (configuration.blocks) 
        {
            configuration.blocks.each(function(block)
            {
                var element = $$(block[1]).first();
                if (element && typeof(classThis.mainBlocks[block[0]]) == 'undefined') 
                {
                    classThis.mainBlocks[block[0]] = element;
                }        
            });
        }
        this.map = configuration.map;
        this.updaterConfig = configuration.updaterConfig;
        this.updaterBlockClass = configuration.updaterBlockClass;
    },
    
    loadingAnimationON: function(targetBlock, configuration)
    {
        var classThis = this;        
        var configValue = '16px';
        Object.keys(configuration).each(function(key)
        {
            if (parseInt(key) > parseInt(configValue) && parseInt(key) < classThis.elementHeight(targetBlock)) 
            {
                configValue = key;
            }
        });
        var classCSS = configuration[configValue];
        if (classCSS) 
        {
            var loadingAnimation = new Element('div');
            loadingAnimation.addClassName(classCSS);
            targetBlock.insertBefore(loadingAnimation, targetBlock.down());
        }        
    },    
    
    actionUrl: function(targetUrl) 
    {
        var founded = targetUrl.match(/onestepcheckout\/ajax\/([^\/]+)\//);
        if (!founded || !founded[1]) { return null; }
        return founded[1];
    },    
    
    removeMessage: function(targetBlock, className)
    {
        var targetBlocks = targetBlock.select("." + className);
        targetBlocks.each(function(element)
        {
            element.remove();
        });
    },    
    
    requestRUN: function(requestUrl, settings) 
    {
        var action = this.actionUrl(requestUrl);
         if (action && this.map[action]) 
        {            
            var classThis = this;
            this.map[action].each(function(block)
            {             
                if (typeof(classThis.mainQueue[block]) === 'undefined') 
                {
                    classThis.mainQueue[block] = 0;                
                }
                if (classThis.mainBlocks[block]) 
                {
                    if (classThis.mainQueue[block] === 0) 
                    {               
                        var targetBlock = classThis.mainBlocks[block].select('.' + classThis.updaterBlockClass).first();
                        if (!targetBlock) { targetBlock = classThis.mainBlocks[block]; }
                        if ("addActionBlocksToQueueBeforeFn" in classThis.mainBlocks[block]) { classThis.mainBlocks[block].addActionBlocksToQueueBeforeFn(); }
                        OnestepcheckoutMain.loadingAnimationON(targetBlock, classThis.updaterConfig);
                        if ("addActionBlocksToQueueAfterFn" in classThis.mainBlocks[block]) { classThis.mainBlocks[block].addActionBlocksToQueueAfterFn(); }
                    }
                    classThis.mainQueue[block]++;
                }            
            });
        }        
        if (this.updaterRequest !== null) { this.updaterQueue.push([requestUrl, settings]); }
        else { this.internalRunRequest(requestUrl, settings); }
    },    
   
    elementHeight: function(selector) 
    {
        var block = $(selector);
        var blockVisibility = block.style.visibility; var blockDisplay = block.style.display; var blockHeight = block.style.height; var blockDimensions = block.getDimensions();
        block.setStyle({            
            'display'    : '',
            'height'     : '',
            'visibility' : 'hidden'
        });
        var newHeight = Math.max(block.getDimensions()['height'], blockDimensions['height']);
        block.setStyle({            
            'display'    : blockDisplay,
            'height'     : blockHeight,
            'visibility' : blockVisibility
        });
        return newHeight;
    },    
   
    removeActionFromQueue: function(currentAction, response) 
    {        
        if (currentAction && this.map[currentAction]) 
        {
            var classThis = this;
            var currentResponse = response || {};
            var newHtml = currentResponse.blocks || {};            
            this.map[currentAction].each(function(block)
            {
                if (classThis.mainBlocks[block]) 
                {
                    classThis.mainQueue[block]--;
                    if (classThis.mainQueue[block] === 0) 
                    {
                        if (newHtml[block]) { classThis.mainBlocks[block].update(newHtml[block]); }
                        if ("removeActionBlocksFromQueueBeforeFn" in classThis.mainBlocks[block]) { classThis.mainBlocks[block].removeActionBlocksFromQueueBeforeFn(response); }                        
                        OnestepcheckoutMain.loadingAnimationOFF(classThis.mainBlocks[block], classThis.updaterConfig);
                        if ("removeActionBlocksFromQueueAfterFn" in classThis.mainBlocks[block]) { classThis.mainBlocks[block].removeActionBlocksFromQueueAfterFn(response); }
                    }
                }                
            });
        }         
    },    
    
    setBlockMessage: function(message, cssClass, parentBlock) 
    {
        var targetBlock = null;        
        if (parentBlock.select("." + cssClass + " ul").length !== 0) 
        { 
            targetBlock = parentBlock.select("." + cssClass + " ul").first();
        } 
        else 
        {
            var errorBlock = new Element('div');
            errorBlock.addClassName(cssClass);
            errorBlock.appendChild(new Element('ul'));
            parentBlock.insertBefore(errorBlock, parentBlock.down());
            targetBlock = errorBlock.down();
        }
        var newMessage = new Element('li');
        newMessage.update(message);
        targetBlock.appendChild(newMessage);
    }
};

OnestepcheckoutMain.initialize();

OnestepcheckoutLogin = Class.create();
OnestepcheckoutLogin.prototype = {
    initialize: function(config){
        var classThis = this;
        this.openPopup = $$(config.openPopupSelector).first();
        this.container = $$(config.containerSelector).first();
        this.forgotPasswordLinkArray = $$(config.forgotPasswordLinkSelector);
        this.backToLoginLinkArray = $$(config.backToLoginLinkSelector);
        this.loginForm = $$(config.loginFormSelector).first();
        this.forgotPasswordForm = $$(config.forgotPasswordFormSelector).first();
        this.forgotPasswordSuccessBlock = $$(config.forgotPasswordSuccessBlockSelector).first();
        this.fbButtonContainer = $$(config.fbButtonContainerSelector).first();
        this.fbButtonRequestUrl = config.fbButtonRequestUrl;
        this.errorMessageBoxCssClass = config.errorMessageBoxCssClass;
        this.overlayConfig = config.overlayConfig;
        this.jsErrorMsg = config.jsErrorMsg;
        
        this.popup = new OnestepcheckoutBox(config.popup);

        this.openPopup.observe(
                'click',
                function () {
                    classThis.loginForm.form.removeClassName('no-display');
                    classThis.forgotPasswordForm.form.addClassName('no-display');
                    classThis.forgotPasswordSuccessBlock.addClassName('no-display');
                    classThis.popup.noDescBoxDisplay();
                });
        
        this.forgotPasswordLinkArray.each(function(link){
            link.observe('click',
            function(e) { 
                OnestepcheckoutMain.removeMessage(classThis.forgotPasswordForm.form, classThis.errorMessageBoxCssClass);
                classThis.forgotPasswordForm.form.setStyle({paddingBottom: '0px'});
                classThis.forgotPasswordForm.validator.reset(); 
                classThis.moveBlockTo(classThis.forgotPasswordForm.form);
            });
        });
        this.backToLoginLinkArray.each(function(link){
            link.observe('click',
            function(e) {
                OnestepcheckoutMain.removeMessage(classThis.loginForm.form, classThis.errorMessageBoxCssClass);        
                classThis.loginForm.form.setStyle({paddingBottom: '0px'});
                classThis.loginForm.validator.reset(); 
                classThis.moveBlockTo(classThis.loginForm.form);
            });
        });

        this.loginForm = new VarienForm(this.loginForm);
        this.forgotPasswordForm = new VarienForm(this.forgotPasswordForm);

        this.loginForm.form.select('button[type=submit]').each(function(btn){
            btn.observe('click',
            function(e){                
                classThis.loginForm.form.select('.validation-advice').each(function(adviceEl){ adviceEl.remove(); });
                if (!classThis.loginForm.validator.validate()) 
                {                    
                    var validationHeight = 0;
                    classThis.loginForm.form.select('.validation-advice').each(function(adviceEl) { if (adviceEl.getHeight() > validationHeight) { validationHeight = adviceEl.getHeight(); } });
                    var newContainerHeight = 0;
                    var formBottomPadding = parseInt(classThis.loginForm.form.getStyle('paddingBottom'));
                    if (formBottomPadding < validationHeight) 
                    {
                        classThis.loginForm.form.setStyle({ paddingBottom: validationHeight + 'px' });
                        newContainerHeight = classThis.loginForm.form.getHeight();
                    } 
                    else 
                    {
                        newContainerHeight = classThis.loginForm.form.getHeight() - formBottomPadding + validationHeight;
                    }                   
                    new Effect.Morph(classThis.container, { style: { height: newContainerHeight + 'px' }, duration: 0.6, afterFinish: function(){ classThis.loginForm.form.setStyle({ paddingBottom: validationHeight + 'px' }); } });
                } 
                else
                {
                    OnestepcheckoutMain.loadingAnimationON(classThis.container, classThis.overlayConfig);
                    classThis.loginForm.form.setStyle({ paddingBottom: '0px' });
                    OnestepcheckoutMain.removeMessage(classThis.loginForm.form, classThis.errorMessageBoxCssClass);
                    new Ajax.Request(classThis.loginForm.form.getAttribute('action'),{ method: 'post', parameters: olegnaxOSCLoginBlock.loginForm.form.serialize(true), onComplete: classThis.ajaxLoginCompleteFunction.bind(classThis) });            
                }
                e.stop();
            });
        });
        this.forgotPasswordForm.form.select('button[type=submit]').each(function(btn){
            btn.observe('click',
                                function(e){                                
                                classThis.forgotPasswordForm.form.select('.validation-advice').each(function(adviceEl){ adviceEl.remove(); });
                                if (classThis.forgotPasswordForm.validator.validate()) 
                                {
                                    OnestepcheckoutMain.loadingAnimationON(classThis.container, classThis.overlayConfig);
                                    classThis.forgotPasswordForm.form.setStyle({ paddingBottom: '0px' });            
                                    OnestepcheckoutMain.removeMessage(classThis.forgotPasswordForm.form, classThis.errorMessageBoxCssClass);            
                                    new Ajax.Request(classThis.forgotPasswordForm.form.getAttribute('action'),{
                                        method: 'post',
                                        parameters: olegnaxOSCLoginBlock.forgotPasswordForm.form.serialize(true),
                                        onComplete: classThis.ajaxForgotPasswordCompleteFunction.bind(classThis)
                                    });
                                } 
                                else 
                                {                                    
                                    var validationHeight = 0;
                                    classThis.forgotPasswordForm.form.select('.validation-advice').each(function(adviceEl) { if (adviceEl.getHeight() > validationHeight) { validationHeight = adviceEl.getHeight(); } });
                                    var newContainerHeight = 0;
                                    var formPaddingBottom = parseInt(classThis.forgotPasswordForm.form.getStyle('paddingBottom'));
                                    if (formPaddingBottom < validationHeight) { classThis.forgotPasswordForm.form.setStyle({ paddingBottom: validationHeight + 'px' }); newContainerHeight = classThis.forgotPasswordForm.form.getHeight();}
                                    else { newContainerHeight = classThis.forgotPasswordForm.form.getHeight() - formPaddingBottom + validationHeight; }                                    
                                    new Effect.Morph(classThis.container, { style: { height: newContainerHeight + 'px' }, duration: 0.6, afterFinish: function(){ classThis.forgotPasswordForm.form.setStyle({ paddingBottom: validationHeight + 'px' }); }});
                                }
                                e.stop();
                            });
        });
        this.currentVisibleBlock = this.loginForm.form;        
        this.container.setStyle({'height': this.currentVisibleBlock.getHeight() + 'px'});
        this.container.select('input, button, a').each(function(el){ el.setAttribute('tabindex', '-1'); });
        this.currentVisibleBlock.select('input, button, a').each(function(el){ el.removeAttribute('tabindex'); });
    },
    
    ajaxLoginCompleteFunction: function(transport){
        var classThis = this;
        try { eval("var json = " + transport.responseText + " || {}"); }
        catch(e) 
        {            
            this.displayError(this.jsErrorMsg,            
            function(){ OnestepcheckoutMain.loadingAnimationOFF(classThis.container, classThis.overlayConfig); });
            return;
        }
        if (json.success) { document.location.reload(); }
        else 
        {
            if (json.redirect_to) { document.location.href = json.redirect_to; return; }
            var errorMessage = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { errorMessage = json.messages; }            
            this.displayError(errorMessage, function(){ OnestepcheckoutMain.loadingAnimationOFF(classThis.container, classThis.overlayConfig);}); }
    },    
    
     moveBlockTo: function(currentBlock){        
        var currentBlock = $(currentBlock);
        this.loginForm.form.addClassName('no-display');
        this.forgotPasswordForm.form.addClassName('no-display');
        this.forgotPasswordSuccessBlock.addClassName('no-display');
        currentBlock.removeClassName('no-display');
        this.currentVisibleBlock = currentBlock;
    },
    
    displayError: function(message, complateFunction)
    {
        OnestepcheckoutMain.messageDisplay(message, this.errorMessageBoxCssClass, this.currentVisibleBlock.select('.error-placeholer').first());        
        var complateFunction = complateFunction || new Function();
        new Effect.Morph(this.container, {
            style: { height: this.currentVisibleBlock.getHeight() + 'px' },
            duration: 0.6,
            afterFinish: function(e){ complateFunction(); }
        });
    },    
    
    ajaxForgotPasswordCompleteFunction: function(transport){
        try { eval("var json = " + transport.responseText + " || {}"); }
        catch(e) 
        {
            var classThis = this;
            this.displayError(this.jsErrorMsg,
            function(){ OnestepcheckoutMain.loadingAnimationOFF(classThis.container, classThis.overlayConfig); });
            return;
        }
        if (json.success) 
        {
           OnestepcheckoutMain.loadingAnimationOFF(this.container, this.overlayConfig);
            this.moveBlockTo(this.forgotPasswordSuccessBlock);
        } 
        else 
        {
            var errorMessage = this.jsErrorMsg;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { errorMessage = json.messages; }
            var classThis = this;
            this.displayError(errorMessage, function(){ OnestepcheckoutMain.loadingAnimationOFF(classThis.container, classThis.overlayConfig); });
        }
    }
};

OnestepcheckoutRelated = Class.create();
OnestepcheckoutRelated.prototype = {
    initialize: function(settings){
        this.mainContainer = $$(settings.containerSelector).first();
        this.productContainer = $$(settings.productContainerSelector).first();        
        this.addProductToWishlistURL = settings.addProductToWishlistURL;
        this.errorMessageBoxClass = settings.errorMessageBoxClass;
        this.jsErrorMessage = settings.jsErrorMessage;        
        this.addProductToCompareListURL = settings.addProductToCompareListURL;        
        this.complateMessageBoxClass = settings.complateMessageBoxClass;
        this.overlayConfig = settings.overlayConfig;
        this.shippingCheckboxContainer = $$(settings.shippingCheckboxContainer).first();
         this.mainContainer = $$(settings.containerSelector).first();                
        var classThis = this;
        this.timerContainer = $$(settings.timer.blockSelector).first();
        this.clockElement = $$(settings.timer.timerClockElSelector).first();
        this.redirectElement = $$(settings.timer.redirectActionElSelector).first();        
        this.overlayConfigTimer = settings.timer.overlayConfig;
        this.cancelElement = $$(settings.timer.cancelActionElSelector).first();
        this.runTimerFrom = parseInt(settings.timer.startTimerFrom);
        if (this.productContainer) 
        {            
            this.productContainer.select('a').each(function(link) { link.observe('click', function(e) { if (e.isLeftClick() || Prototype.Browser.IE) { classThis.clickAction(link); e.stop(); } }); });
            this.productContainer.select('button').each(function(button)
            {
                button.setAttribute('_onclick', button.getAttribute('onclick'));
                button.removeAttribute('onclick');
                button.observe('click', function(e)
                {
                    classThis.clickAction(button);
                    e.stop();
                });
            });
        }        
    },
    
    timerStart: function()
    {
        var classThis = this;
        this._intervalId = window.setInterval(function()
        {
            var currentTime = parseInt(classThis.clockElement.innerHTML);
            currentTime--;
            if (currentTime > 0) 
            {
                classThis.clockElement.update(currentTime);
            } 
            else if(currentTime === 0)
            {
                classThis.redirectWhenClick();
            }
        }, 1000);
    },
    
    addTimerFunction: function(func) 
    {
        this.doTimerAction = func;
    },
    
    redirectWhenClick: function(e) 
    {       
        clearInterval(this._intervalId);
        this.clockElement.update(0);
        this.cancelElement.hide();
        this.redirectElement.hide();
        this.doTimerAction();        
        var classThis = this;
        window.setInterval(function()
        {
            classThis.doTimerAction();
        }, 25000);                           
        var height = OnestepcheckoutMain.elementHeight(this.timerContainer);
        var classNameConfigKey = '16px';
        Object.keys(this.overlayConfigTimer).each(function(key)
        {
            if (parseInt(key) < height && parseInt(key) > parseInt(classNameConfigKey)) 
            {
                classNameConfigKey = key;
            }
        });
        var classCSS = this.overlayConfigTimer[classNameConfigKey];        
        
        if (classCSS) 
        {
            this.timerContainer.addClassName(classCSS);
        }
    },    
   
    timerDisplay: function() {
        var classThis = this;
        this.clockElement.update(this.runTimerFrom);
        this.cancelElement.show();
        this.redirectElement.show();
        this.timerContainer.setStyle({display: 'block'});
        this.redirectElement.observe(
                'click',
                this.redirectWhenClick.bind(this));
        this.cancelElement.observe(
                'click',
                function(e) 
                {
                    clearInterval(classThis._intervalId);
                    classThis.timerContainer.setStyle({display: 'none'});
                    classThis.redirectElement.stopObserving('click');
                    classThis.cancelElement.stopObserving('click');                    
                    Object.values(classThis.overlayConfigTimer).each(function(cssClassString)
                    {
                        classThis.timerContainer.removeClassName(cssClassString);
                    });
                });
    },
    
    clickAction: function(el) 
    {
        var originalAction = "";
        var originalActionFunction = null;
        if (el.tagName.toUpperCase() === "A" && el.getAttribute('href') !== null && el.getAttribute('href') !== '#') 
        {
            originalAction = el.getAttribute('href');
            originalActionFunction = function() { window.location.href = el.getAttribute('href'); };
        }
        else if (el.tagName.toUpperCase() === "BUTTON" && el.getAttribute('_onclick') !== null)
        {
            originalAction = el.getAttribute('_onclick');
            originalActionFunction = function() { eval(el.getAttribute('_onclick')); };
        }
        else { return; }
        var isActionAddToWishlist = false;
        if (originalAction.indexOf('wishlist/index/add/product/') !== -1) { isActionAddToWishlist = true; }        
        if (isActionAddToWishlist) 
        { 
            OnestepcheckoutMain.loadingAnimationON(this.mainContainer, this.overlayConfig);
            OnestepcheckoutMain.removeMessage(this.mainContainer, this.errorMessageBoxClass);
            OnestepcheckoutMain.removeMessage(this.mainContainer, this.complateMessageBoxClass);
            new Ajax.Request(this.addProductToWishlistURL, {
                parameters: {product: originalAction.match("add/product/([0-9]+)/")[1]},
                onComplete: this.ajaxCompleteFunction.bind(this)
            });
            return;
        }
        var isActionAddToCompareList = false;
        if (originalAction.indexOf('compare/add/product/') !== -1) { isActionAddToCompareList = true; }
        if (isActionAddToCompareList) 
        {
            OnestepcheckoutMain.loadingAnimationON(this.mainContainer, this.overlayConfig);
            OnestepcheckoutMain.removeMessage(this.mainContainer, this.errorMessageBoxClass);
            OnestepcheckoutMain.removeMessage(this.mainContainer, this.complateMessageBoxClass);
            new Ajax.Request(this.addProductToCompareListURL, {
                parameters: {product: originalAction.match("add/product/([0-9]+)/")[1]},
                onComplete: this.ajaxCompleteFunction.bind(this)
            });
            return;
        }        
        this.addTimerFunction(function()
        {
            try { originalActionFunction(); }
            catch(ex) { }
        });
        this.timerDisplay();
        this.timerStart();
    }, 
    
    displayError: function(message)
    {
        OnestepcheckoutMain.messageDisplay(message, this.errorMessageBoxClass, this.productContainer);        
        var classThis = this;
        this.productContainer.select("." + this.errorMessageBoxClass + " a").each(function(link){ link.observe('click', function(e)
            { 
                if (e.isLeftClick() || Prototype.Browser.IE) { classThis.clickAction(link); e.stop(); }
            });
        });
    },
    
    ajaxCompleteFunction: function(transport) {
        try { eval("var json = " + transport.responseText + " || {}"); }
        catch(e) 
        {
            this.displayError(this.jsErrorMessage);
            OnestepcheckoutMain.loadingAnimationOFF(this.mainContainer, this.overlayConfig);
            return;
        }
        if (!json.success) 
        {
            var errorMessage = this.jsErrorMessage;
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { errorMessage = json.messages; }
            this.displayError(errorMessage);
        }
        else
        {
            if ("blocks" in json) 
            { 
                if (json.blocks.related) 
                {
                    var mass = new Element('div');
                    mass.innerHTML = json.related;            
                    this.productContainer.update(mass.select('#' + this.productContainer.getAttribute('id')).first().innerHTML);
                    var classThis = this;
                    this.productContainer.select('a').each(function(link) { link.observe('click', function(e) { if (e.isLeftClick() || Prototype.Browser.IE) { classThis.onActionClick(link); e.stop(); } }); });
                    this.productContainer.select('button').each(function(button)
                    {
                        button.setAttribute('_onclick', button.getAttribute('onclick'));
                        button.removeAttribute('onclick');
                        button.observe('click', function(e)
                        {
                            classThis.onActionClick(button);
                            e.stop();
                        });
                    });
                }
                if (json.blocks.top_links) 
                {
                    var mass = new Element('div');
                    mass.innerHTML = json.top_links;
                    var topLinksClass = mass.down().getAttribute('class');
                    if (topLinksClass) 
                    {
                        var targetBlock = $$(".quick-access ." + topLinksClass).first();
                        if (targetBlock) { targetBlock.innerHTML = mass.down().innerHTML; }
                    }
                } 
                OnestepcheckoutMain.removeActionFromQueue(OnestepcheckoutMain.actionUrl(transport.request.url), json);
                if ("can_shop" in json && json.can_shop) { this.shippingCheckboxContainer.removeClassName('no-display') }
            }
            if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) 
            {                              
                OnestepcheckoutMain.messageDisplay(json.messages, this.complateMessageBoxClass, this.productContainer);                
                var classThis = this;
                this.productContainer.select("." + this.complateMessageBoxClass + " a").each(function(currentLink)
                {
                    currentLink.observe('click', function(e) { if (e.isLeftClick() || Prototype.Browser.IE) { classThis.clickAction(currentLink); e.stop(); }
                    });
                });                 
            }
        }
        OnestepcheckoutMain.loadingAnimationOFF(this.mainContainer, this.overlayConfig);
    }
};

OnestepcheckoutBox = Class.create();
OnestepcheckoutBox.prototype = {
    initialize: function(config) {
        var classThis = this;
        this.boxContainer = $$(config.containerSelector).first();
        this.contentContainer = $$(config.contentContainerSelector).first();
        this.acceptContainer = $$(config.acceptContainerSelector).first();
        this.overlay = $$(config.overlaySelector).first();
        this.buttons = {close: {enabled: config.buttons.close.enabled, el: $$(config.buttons.close.selector).first(), onClickFn: config.buttons.close.onClickFn || Prototype.emptyFunction },
            close2: {enabled: config.buttons.close2.enabled, el: $$(config.buttons.close2.selector).first(), onClickFn: config.buttons.close2.onClickFn || Prototype.emptyFunction } ,
            accept: { enabled: config.buttons.accept.enabled, el: $$(config.buttons.accept.selector).first(), onClickFn: config.buttons.accept.onClickFn || Prototype.emptyFunction }}
        this.isVisible = false;
        if (this.buttons.close.el) { this.buttons.close.el.observe('click', this.onCloseEvent.bind(this)); }
        if (this.buttons.close2.el) { this.buttons.close2.el.observe('click', this.onCloseEvent.bind(this)); }
        if (this.buttons.accept.el) { this.buttons.accept.el.observe('click', function(e) { if (classThis.buttons.accept.enabled) { e.stop(); classThis.buttons.accept.onClickFn(e); classThis.onCloseEvent(e);
                        } 
                    });
        }
    },
    
    onCloseEvent: function(e) 
    {
        if (this.buttons.close.enabled) 
        {
            e.stop();
            this.buttons.close.onClickFn(e);
            if (this.isVisible) 
            {
                this.isVisible = false;                
                var classThis = this;
                this.hideEffect(this.overlay);
                this.overlay.stopObserving(
                'click',
                function(e) 
                {
                    classThis.onCloseEvent(e);                   
                });                
                this.hideEffect(this.boxContainer);
                Event.stopObserving(window, 'resize', this.resizeBox.bind(this));
            }            
        }        
    },
     
    descBoxDisplay: function(descriptionText) {
        if (!this.isVisible) 
        {
            this.isVisible = true;
            this.contentContainer.setStyle({'height': 'auto'});
            this.contentContainer.update(descriptionText);            
            this.boxContainer.setStyle({'display': 'block', 'left' : '9999999px'});
            this.resizeBox();
            this.boxContainer.setStyle({'display': 'none'});
            var classThis = this;
            this.displayEffect(this.overlay);
            this.overlay.observe(
                'click',
                function(e) 
                {
                    classThis.onCloseEvent(e);                 
                });            
            this.displayBox();
            Event.observe(window, 'resize', this.resizeBox.bind(this));
        }        
    },
    
    displayEffect: function(element) 
    {
        var defaultStyle = { '-moz-opacity': (element.getStyle('-moz-opacity') || '1') + "", 'opacity':  (element.getStyle('opacity') || '1') + "", 'filter':  (element.getStyle('filter') || 'alpha(opacity=100)') + "" }
        element.setStyle({ '-moz-opacity': '0', 'opacity': '0', 'filter': 'alpha(opacity=0)', 'display': 'block' });
        new Effect.Morph(element, { style: defaultStyle, duration: 0.3 });
    },    

    noDescBoxDisplay: function() 
    {
        if (!this.isVisible) 
        {
            this.isVisible = true;
            this.boxContainer.setStyle({'display': 'block', 'left' : '9999999px'});
            this.resizeBox();
            this.boxContainer.setStyle({'display': 'none'});
            var classThis = this;
            this.displayEffect(this.overlay);
            this.overlay.observe(
                'click',
                function(e) 
                {
                    classThis.onCloseEvent(e);                  
                });
            this.displayBox();
            Event.observe(window, 'resize', this.resizeBox.bind(this));
        }       
    },    
    
    hideEffect: function(el) 
    {
        var defaultStyle = {
            '-moz-opacity': (el.getStyle('-moz-opacity') || '1') + "",
            'opacity':  (el.getStyle('opacity') || '1') + "",
            'filter':  (el.getStyle('filter') || 'alpha(opacity=100)') + ""
        }
        new Effect.Morph(el, {
            style: {
                '-moz-opacity': '0',
                'opacity': '0',
                'filter': 'alpha(opacity=0)'
            },
            duration: 0.3,
            afterFinish: function() {
                el.setStyle({
                    'display': 'none'
                });
                el.setStyle(defaultStyle);
            }
        });
    },    
    
    displayBox: function() {
        Object.values(this.buttons).each(function(button)
        {
            if (button.el)
            {
                if (button.enabled) 
                {
                    button.el.show();
                }
                else 
                {
                    button.el.hide();
                }
            }            
        });
        this.displayEffect(this.boxContainer);
    },
        
    resizeBox: function() 
    {
        this.boxContainer.setStyle({height: 'auto'});
        this.contentContainer.setStyle({height: 'auto'});
        var boxTop = (document.viewport.getHeight() - this.boxContainer.getHeight())/2;
        var boxLeft = (document.viewport.getWidth() - this.boxContainer.getWidth())/2;
        if (boxTop < 50) 
        {
            boxTop = 50;
            this.boxContainer.setStyle({height: (document.viewport.getHeight() - 100) + 'px'});
        }
        var height = this.boxContainer.getHeight() - parseInt(this.boxContainer.getStyle('padding-top')) - parseInt(this.boxContainer.getStyle('padding-bottom')) - this.acceptContainer.getHeight();
        this.contentContainer.setStyle({'height': height + 'px'});
        this.boxContainer.setStyle({ left: boxLeft + 'px', top: boxTop + 'px' });
    }     
};
