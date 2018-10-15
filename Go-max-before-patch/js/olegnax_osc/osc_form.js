OnestepcheckoutReviewCart = Class.create();
OnestepcheckoutReviewCart.prototype = {
    initialize: function(settings){
        this.settings = settings;            
        this.cartUpdateURL = settings.cartUpdateURL;
        this.ajaxRequestId = 0;
        this.imbricateSettings = settings.imbricateSettings;
        this.updateInProcess = false;
        var classThis = this;
        setTimeout(function () 
        {           
            $$(classThis.settings.removeLinkSelector).each(function(element){ element.observe(
                        'click',
                        function(event) {
                            Event.stop(event);        
                            var link;
                            var input;
                            var line;
                            link = $(event.target);
                            line = link.up('tr');
                            input = line.select('input.qty').first();
                            input.value = 0;
                            classThis.cartUpdate();
                        }); });
            $$(classThis.settings.updateQtySelector).each(function(element){ element.observe(
                        'click',
                        function(event) {
                        var inputElement; var cell; var adjustQTY; var btn; var qty;
                        Event.stop(event);
                        //classThis.updateInProcess = true;
                        btn = $(event.target);
                        btn = btn.hasClassName('button') ? btn : btn.up('button');
                        cell = btn.up('td');
                        inputElement = cell.select('input.qty').first();
                        adjustQTY = btn.hasClassName('adjust-qty-up') ? 1 : -1;
                        qty = parseFloat(inputElement.value);
                        if (isNaN(qty)) { qty = 0; }
                        qty = qty + adjustQTY;
                        if (qty < 0) { qty = 0; }
                        inputElement.value = qty;
                        //classThis.cartUpdate();
                    }); });
            $$(classThis.settings.updateQtyButton).each(function(element){                
                element.observe(
                        'click',
                        function(event) 
                        {
                        if (!classThis.updateInProcess) 
                        {
                            Event.stop(event);
                            classThis.cartUpdate();
                        }
                        else
                        {
                            classThis.updateInProcess = false;
                        }
                        }); });
            
        }, 1);
        Event.observe(window, 'dom:loaded', function(e) {
            classThis.relatedBlockContainer = $$(classThis.settings.relatedBlockContainerSelector).first();
        });
    },
   
    cartUpdate: function ()
    {
        this.ajaxRequestId++;
        var thisRequestId = this.ajaxRequestId;
        var classThis = this;
        var requestSettings = {
            method: 'post',
            parameters: Form.serializeElements($$(this.settings.qtySelector)),
            onComplete: function(transportData){
                if (thisRequestId !== classThis.ajaxRequestId) { return; }
                try 
                {
                    eval("var json = " + transportData.responseText + " || {}");
                } 
                catch(e) 
                {
                    if (!classThis.relatedBlockContainer) { return; }
                    OnestepcheckoutMain.loadingAnimationOFF(classThis.relatedBlockContainer, classThis.imbricateSettings);
                    return;
                }
                if (json.redirect) 
                {
                    setLocation(json.redirect); return;
                }
                if (json.success) 
                {
                    if ("blocks" in json) 
                    {       
                        if (classThis.relatedBlockContainer && json.blocks.related) 
                        {
                            var box = new Element('div');
                            box.innerHTML = json.blocks.related;            
                            classThis.relatedBlockContainer.update(box.select('#' + classThis.relatedBlockContainer.getAttribute('id')).first().innerHTML);
                        }
                        if (json.blocks.minicart_head) 
                        {
                            var block = $$(".header-minicart").first();
                            if (block) { block.innerHTML = json.blocks.minicart_head; }
                        }                        
                        classThis.relatedBlockContainer = $$(classThis.settings.relatedBlockContainerSelector).first();
                    }
                }                 
                if (!classThis.relatedBlockContainer) { return; }
                OnestepcheckoutMain.loadingAnimationOFF(classThis.relatedBlockContainer, classThis.imbricateSettings);
            }
        };        
        OnestepcheckoutMain.requestRUN(this.cartUpdateURL, requestSettings);
    }
};

OnestepcheckoutPayment = Class.create();
OnestepcheckoutPayment.prototype = {
    initialize: function(settings) 
    {
        this.methodsInputsSwitch = $$(settings.methodsInputsSwitchSelector);
        this.containerWraper = $$(settings.containerWraperSelector).first();        
        this.additionalPrefix = settings.additionalPrefix;
        this.savedData = {};
        this.savePaymentURL = settings.savePaymentURL;        
        this.mainContainer = $$(settings.mainContainerSelector).first();        
        var classThis = this;

        this.cvv = {};
        this.cvv.tooltip = $$(settings.cvv.tooltipSelector).first();
        this.cvv.closeEl = $$(settings.cvv.closeElSelector).first();
        this.cvv.triggerEls = $$(settings.cvv.triggerElsSelector);

        if (navigator.userAgent.indexOf("MSIE 8.0") == -1) 
        {
            this.setHack();            
            
            this.methodsInputsSwitch.each(function(el) 
            {
                var code = el.value;                
                if ($(classThis.additionalPrefix + code)) { $(classThis.additionalPrefix + code).setStyle({'overflow':'hidden','display':'none'}) }
                if (!el.checked) 
				{
					classThis.hideAdditional(code);
				}
                else 
                {
                    classThis.displayAdditional(code);
                    classThis.currentMethod = code;
                }
            });            
            this.setObservers();
        } 
        else 
        {            
            Event.observe(window, 'load', function(e)
            {
                classThis.setHack();
               
                this.methodsInputsSwitch.each(function(el)
                {
                    var code = el.value;                    
                    if ($(classThis.additionalPrefix + code)) { $(classThis.additionalPrefix + code).setStyle({'overflow':'hidden','display':'none'}) }
                    if (!el.checked) { classThis.hideAdditional(code); }
                    else 
                    {
                        classThis.displayAdditional(code);
                        classThis.currentMethod = code;
                    }
                });
                
                classThis.setObservers();
            });
        }
    },
   
    setHack: function() {
        window.payment = window.payment || {};
        window.payment.switchMethod = Prototype.emptyFunction;        
        window.checkout = { setLoadWaiting: Prototype.emptyFunction, accordion: { container: { readAttribute: Prototype.emptyFunction } }, steps: [], gotoSection: Prototype.emptyFunction };        
        window.document.body.appendChild(new Element('div', {'id': 'checkout-review-submit', 'style': 'display:none'}));
        window.document.body.appendChild(new Element('div', {'id': 'iframe-warning', 'style': 'display:none'}));
    },
   
    setObservers: function() 
    {
        var classThis = this;        
        this.cvv.triggerEls.each(function(el) { el.observe('click', classThis.whenTooltipTriggerClick.bind(classThis));  });        
        if(this.cvv.closeEl) { this.cvv.closeEl.observe('click', 
            function(event)
            {
                Event.stop(event);                
                classThis.cvv.tooltip.setStyle({'display': 'none' });                
            })
        }
        this.methodsInputsSwitch.each(function(el) 
        {
            el.observe('click', function(e) 
            {
                if (classThis.currentMethod !== el.value) 
                {
                    if (classThis.currentMethod && $(classThis.additionalPrefix + classThis.currentMethod)) 
                    {                
                        classThis.hideAdditional(classThis.currentMethod);
                        $(classThis.additionalPrefix + classThis.currentMethod).fire('payment-method:switched-off', {method_code : classThis.currentMethod});
                    }
                    if (!$(classThis.additionalPrefix + el.value))
                    {                        
                        document.body.fire('payment-method:switched', {method_code : el.value});
                    } 
                    else 
                    {
                        classThis.displayAdditional(el.value);
                        $(classThis.additionalPrefix + el.value).fire('payment-method:switched', {method_code : el.value});
                    }
                    classThis.currentMethod = el.value;
                    var currentBlock = classThis.additionalPrefix + classThis.currentMethod;
                    var validationCheck = true;
                    [currentBlock + '_before', currentBlock, currentBlock + '_after'].each(function(el) 
                    {
                        var el = $(el);
                        if (!el) { return; }                      
                        Form.getElements(el).each(function(vEelement)
                        {
                            var cn = $w(vEelement.className);
                            validationCheck = validationCheck && cn.all(function(name) 
                            {
                                var v = Validation.get(name);
                                try 
                                {
                                    if(Validation.isVisible(vEelement) && !v.test($F(vEelement), vEelement)) { return false; }
                                    else { return true; }
                                }
                                catch(e) { return true; }
                            });
                        })
                    });
                    if (!validationCheck) { return; }
                    window.payment.currentMethod = classThis.currentMethod;
                    OnestepcheckoutMain.requestRUN(classThis.savePaymentURL, 
                    {
                        method: 'post',
                        parameters: Form.serialize(classThis.mainContainer, true)
                    });
                }
            });
            var currentBlock = classThis.additionalPrefix + el.value;
            [currentBlock + '_before', currentBlock, currentBlock + '_after'].each(function(elId)
            {
                var el = $(elId);
                if (!el) { return; }                
            });
        });        
        if (!this.containerWraper.addActionBlocksToQueueAfterFn) 
        {
            this.containerWraper.addActionBlocksToQueueAfterFn = function() 
            {
                classThis.savedData = {};
                Form.getElements(classThis.containerWraper).each(function(el){
                    var elId = el.getAttribute('id');
                    if (elId) { classThis.savedData[elId] = el.getValue(); }
                });
            }
        }
        if (!this.containerWraper.removeActionBlocksFromQueueAfterFn) 
        {
            this.containerWraper.removeActionBlocksFromQueueAfterFn = function() 
            {
                Form.getElements(classThis.containerWraper).each(function(el)
                {
                    var elId = el.getAttribute('id');
                    if (elId in classThis.savedData) { el.setValue(classThis.savedData[elId]); }
                });
                classThis.savedData = {};
            }
        }
    },    
   
    whenTooltipTriggerClick: function(e) 
    {
        if(this.cvv.tooltip)
        {
            this.cvv.tooltip.setStyle({ top: (Event.pointerY(e) - 560)+'px' });
            this.cvv.tooltip.toggle();           
            this.cvv.tooltip.setStyle({ '-moz-opacity': '0', 'opacity': '0', 'filter': 'alpha(opacity=0)', 'display': 'block' });
            new Effect.Morph(this.cvv.tooltip, { style: { '-moz-opacity': (this.cvv.tooltip.getStyle('-moz-opacity') || '1') + "", 'opacity':  (this.cvv.tooltip.getStyle('opacity') || '1') + "", 'filter':  (this.cvv.tooltip.getStyle('filter') || 'alpha(opacity=100)') + "" }, duration: 0.3 });
        }       
        e.stop();
    }, 
    
    displayAdditional: function(methodCode) 
    {
        var classThis = this;
        var currentBlock = this.additionalPrefix + methodCode;
        [currentBlock + '_before', currentBlock, currentBlock + '_after'].each(function(el) 
        {
            var el = $(el);
            if (el) 
            {                
                el.setStyle({'display': '', height: '0px'});               
                classThis.setEffect(el, OnestepcheckoutMain.elementHeight(el), 0.5, function(){ el.setStyle({'height': ''}); });                
                el.select('input', 'select', 'textarea', 'button').each(function(field) { field.disabled = false; });
            }
        });
    },    
    
    hideAdditional: function(methodCode) {
        var classThis = this;
        var currentBlock = this.additionalPrefix + methodCode;
        [currentBlock + '_before', currentBlock, currentBlock + '_after'].each(function(el) 
        {
            var el = $(el);
            if (el) 
            {                
                classThis.setEffect(el, 0, 0.5, function(){ el.setStyle({'display': 'none'}); });               
                el.select('input', 'select', 'textarea', 'button').each(function(field) { field.disabled = true; });
            }
        });
    },
    
    setEffect: function(el, newHeight, timeout, complateFunction) 
    {
        if (el.effect) { el.effect.cancel(); }
        var complateFunction = complateFunction || Prototype.emptyFunction;
        el.effect = new Effect.Morph(el, { style: { 'height': newHeight + 'px' }, duration: timeout, afterFinish: function(){ delete el.effect; complateFunction(); } });
    }
};

OnestepcheckoutReviewCoupon = Class.create();
OnestepcheckoutReviewCoupon.prototype = {
    initialize: function(settings) {        
        this.messageContainer = $$(settings.messageContainerSelector).first();
        this.couponCode = $(settings.couponCode);       
        this.couponApplyURL = settings.couponApplyURL;        
        this.complateMessageBoxClass = settings.complateMessageBoxClass;
        this.crashMessageBoxClass = settings.crashMessageBoxClass;
        this.jsCrashMessage = settings.jsCrashMessage;
        this.jsComplateMessage = settings.jsComplateMessage;        
        this.couponApplied = settings.couponApplied;        
        this.isApplyCouponButton = settings.isApplyCouponButton;
        this.applyCouponButton = $$(settings.applyCouponButtonSelector).first();
        this.cancelCouponButton = $$(settings.cancelCouponButtonSelector).first();       
        this.ajaxRequestId = 0;
        if (this.isApplyCouponButton) 
        {
            if (this.applyCouponButton) 
            {
                this.applyCouponButton.observe('click', this.applyCoupon.bind(this));
                this.cancelCouponButton.observe('click', this.applyCoupon.bind(this));
            }
        } 
        else 
        {
            if (this.couponCode) { this.couponCode.observe('change', this.applyCoupon.bind(this)); }
        }
    },     
    
    applyCoupon: function(e) {
        this.dellMessage();
        if (this.isApplyCouponButton) 
        {
            if (!this.couponApplied) 
            {
                this.couponCode.addClassName('required-entry');
                var validationResult = Validation.validate(this.couponCode)
                this.couponCode.removeClassName('required-entry');
                if (!validationResult) { return; }
            } 
            else { this.couponCode.setValue(''); }
        } else { if (!this.couponCode.getValue() && !this.couponApplied) { return; }}        
        this.ajaxRequestId++;
        var thisRequestId = this.ajaxRequestId;
        var classThis = this;
        var requestOptions = {
            method: 'post',
            parameters: { coupon_code: this.couponCode.getValue() },
            onComplete: function(transportData)
            {
                if (thisRequestId !== classThis.ajaxRequestId) { return; }
                try { eval("var json = " + transportData.responseText + " || {}"); }
                catch(e) { classThis.displayError(classThis.jsCrashMessage); return; }
                classThis.couponApplied = json.coupon_applied;
                if (!json.success) 
                {
                    var errorMsg = this.jsCrashMessage;
                    if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { errorMsg = json.messages; }
                    classThis.displayError(errorMsg);
                } 
                else 
                { 
                    var successMesssage = classThis.jsComplateMessage;
                    if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { successMesssage = json.messages; }
                    classThis.displayOK(successMesssage);
                    if (!classThis.couponApplied) 
                    {
                        classThis.applyCouponButton.show();
                        classThis.cancelCouponButton.hide();
                    }
                    else 
                    {
                        classThis.applyCouponButton.hide();
                        classThis.cancelCouponButton.show();
                    }           
                }
            }
        };
        OnestepcheckoutMain.requestRUN(this.couponApplyURL, requestOptions);
    },   
   
    displayError: function(message, complateFunction){
        OnestepcheckoutMain.messageDisplay(message, this.crashMessageBoxClass, this.messageContainer);        
        var complateFunction = complateFunction || new Function();
        new Effect.Morph(this.messageContainer, {
            style: { height: this.messageContainer.down().getHeight() + 'px' },
            duration: 0.3,
            afterFinish: function(e){ complateFunction(); }
        });
    },    
    
    displayOK: function(message, complateFunction){
        OnestepcheckoutMain.messageDisplay(message, this.complateMessageBoxClass, this.messageContainer);        
        var complateFunction = complateFunction || new Function();
        new Effect.Morph(this.messageContainer, {
            style: { height: this.messageContainer.down().getHeight() + 'px' },
            duration: 0.3,
            afterFinish: function(e){ complateFunction(); }
        });
    },    
    
    dellMessage: function() {
        if (this.messageContainer.down()) 
        {
            var classThis = this;
            new Effect.Morph(this.messageContainer, { style: { height: 0 + 'px' },
                duration: 0.3,
                afterFinish: function(e) 
                {
                    OnestepcheckoutMain.removeMessage(classThis.messageContainer, classThis.crashMessageBoxClass);
                    OnestepcheckoutMain.removeMessage(classThis.messageContainer, classThis.complateMessageBoxClass);
                }
            });
        }
    }
};

OnestepcheckoutReviewEnterpriseStorecredit = Class.create();
OnestepcheckoutReviewEnterpriseStorecredit.prototype = {
    initialize: function(settings) {       
        this.messageContainer = $$(settings.creditMessageContainerSelector).first();
        this.storecreditCheckbox = $(settings.storecreditCheckbox);
        this.setStorecreditURL = settings.setStorecreditURL;
        this.complateMessageBoxClass = settings.complateMessageBoxClass;
        this.crashMessageBoxClass = settings.crashMessageBoxClass;
        this.jsCrashMessage = settings.jsCrashMessage;
        this.jsComplateMessage = settings.jsComplateMessage;
        this.ajaxRequestId = 0;
        if (this.storecreditCheckbox) 
        {
            var classThis = this;
            this.storecreditCheckbox.observe(
                    'change',
                    function() {
                        if (classThis.messageContainer.down())
                        {                            
                            new Effect.Morph(classThis.messageContainerSelector, { style: { height: 0 + 'px' },
                                duration: 0.3,
                                afterFinish: function(e) 
                                {
                                    OnestepcheckoutMain.removeMessage(classThis.messageContainerSelector, classThis.crashMessageBoxClass);
                                    OnestepcheckoutMain.removeMessage(classThis.messageContainerSelector, classThis.complateMessageBoxClass);
                                }
                            });
                        }
                        classThis.ajaxRequestId++;
                        var thisRequestId = classThis.ajaxRequestId;
                        var requestSettings = {
                            method: 'post',
                            parameters: { use_customer_balance: classThis.storecreditCheckbox.getValue() },
                            onComplete: function(transport) 
                            {
                                if (classThis.ajaxRequestId === thisRequestId)
                                {
                                    try { eval("var json = " + transport.responseText + " || {}"); }
                                    catch(e) { classThis.displayError(classThis.jsCrashMessage); return; }
                                    classThis.isPointsApplied = json.points_applied;
                                    if (!json.success) 
                                    {
                                        var errorMessage = classThis.jsCrashMessage;
                                        if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { errorMessage = json.messages; }
                                        classThis.displayError(errorMessage);
                                    } 
                                    else 
                                    {
                                        var successMessage = classThis.jsComplateMessage;
                                        if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { successMessage = json.messages; }
                                        OnestepcheckoutMain.messageDisplay(successMessage, classThis.complateMessageBoxClass, classThis.messageContainerSelector);                                        
                                        var complateFunction = new Function();
                                        new Effect.Morph(classThis.messageContainerSelector, { style: { height: classThis.messageContainerSelector.down().getHeight() + 'px' },
                                            duration: 0.3,
                                            afterFinish: function(e){ complateFunction(); }
                                        });
                                    }
                                }                                
                            }
                        };
                        OnestepcheckoutMain.requestRUN(classThis.setStorecreditURL, requestSettings);
                    });
        }
    },
    
    displayError: function(message, complateFunction){
        OnestepcheckoutMain.messageDisplay(message, this.crashMessageBoxClass, this.messageContainerSelector);        
        var complateFunction = complateFunction || new Function();
        new Effect.Morph(this.messageContainerSelector, { style: { height: this.messageContainerSelector.down().getHeight() + 'px' },
            duration: 0.3,
            afterFinish: function(e){ complateFunction(); }
        });
    }
};

OnestepcheckoutReviewEnterprisePoints = Class.create();
OnestepcheckoutReviewEnterprisePoints.prototype = {
    initialize: function(settings) {       
        this.messageContainer = $$(settings.pointsMessageContainerSelector).first();
        this.pointsCheckbox = $(settings.pointsCheckbox);
        this.pointsApplyURL = settings.pointsApplyURL;
        this.complateMessageBoxClass = settings.complateMessageBoxClass;
        this.crashMessageBoxClass = settings.crashMessageBoxClass;
        this.jsCrashMessage = settings.jsCrashMessage;
        this.jsComplateMessage = settings.jsComplateMessage;
        this.ajaxRequestId = 0;  
        if (this.pointsCheckbox) {
            var classThis = this;
            this.pointsCheckbox.observe(
                    'change',
                    function() {                                                
                        if (classThis.messageContainer.down()) 
                        {                            
                            new Effect.Morph(classThis.messageContainer, {
                                style: { height: 0 + 'px' },
                                duration: 0.3,
                                afterFinish: function(e) 
                                {
                                    OnestepcheckoutMain.removeMessage(classThis.messageContainer, classThis.crashMessageBoxClass);
                                    OnestepcheckoutMain.removeMessage(classThis.messageContainer, classThis.complateMessageBoxClass);
                                }
                            });
                        }                        
                        classThis.ajaxRequestId++;
                        var thisRequestId = classThis.ajaxRequestId;
                        var requestSettings = {
                            method: 'post',
                            parameters: { use_reward_points: classThis.pointsCheckbox.getValue() },
                            onComplete: function(transport) { if (thisRequestId !== classThis.ajaxRequestId) { return; }                                
                                try { eval("var json = " + transport.responseText + " || {}"); }
                                catch(e) { classThis.displayError(classThis.jsCrashMessage); return; }
                                classThis.isPointsApplied = json.points_applied;
                                if (json.success) {
                                    var successMessage = classThis.jsComplateMessage;
                                    if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { successMessage = json.messages; }
                                    OnestepcheckoutMain.messageDisplay(successMessage, classThis.complateMessageBoxClass, classThis.messageContainer);                                    
                                    var complateFunction = new Function();
                                    new Effect.Morph(this.messageContainer, { style: { height: classThis.messageContainer.down().getHeight() + 'px' },
                                        duration: 0.3,
                                        afterFinish: function(e){ complateFunction(); }
                                    });                                    
                                }
                                else 
                                {
                                    var errorMessage = classThis.jsCrashMessage;
                                    if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) {
                                        errorMessage = json.messages;
                                    }
                                    classThis.displayError(errorMessage);
                                }                                
                            }
                        };
                        OnestepcheckoutMain.requestRUN(classThis.pointsApplyURL, requestSettings);
                    });
        }
    },
    
    displayError: function(message, complateFunction){
        OnestepcheckoutMain.messageDisplay(message, this.crashMessageBoxClass, this.messageContainer);       
        var complateFunction = complateFunction || new Function();
        new Effect.Morph(this.messageContainer, { style: { height: this.messageContainer.down().getHeight() + 'px' },
            duration: 0.3,
            afterFinish: function(e){ complateFunction(); }
        });
    }
};

OnestepcheckoutReviewComments = Class.create();
OnestepcheckoutReviewComments.prototype = {
    initialize: function(settings) {
        this.mainContainer = $$(settings.containerSelector).first();
        this.newRowsCount = settings.newRowsCount;
        this.saveDataURL = settings.saveDataURL;
        var classThis = this;
        this.mainContainer.select('textarea').each(function(textarea) {
            textarea.setStyle({
                'overflow-y': 'hidden'
            });            
            var basicRowCount = parseInt(textarea.getAttribute('rows'));
            var basicScrollHeight = textarea.scrollHeight;        
            var basicHeight = parseInt(textarea.getStyle('height'));            
            textarea.observe('focus', function(e){
                var thisRowCount = basicRowCount +
                    (((textarea.scrollHeight - basicScrollHeight) * basicRowCount) /  basicHeight);
                if (thisRowCount < classThis.newRowsCount) {
                    thisRowCount = classThis.newRowsCount;
                } else {
                    thisRowCount++; 
                }
                var thisHeight = (basicHeight/basicRowCount)*thisRowCount;
                classThis.changeRows(textarea, thisRowCount, thisHeight, function(){
                    textarea.setStyle({
                        'overflow-y': 'auto'
                    });
                });
            });
            textarea.observe('blur', function(e){
                var lengthOfValue = textarea.getValue().strip().length;
                if (lengthOfValue === 0) {
                    classThis.changeTextareas(textarea, function(){
                        textarea.setStyle({
                            'overflow-y': 'hidden'
                        });
                        classThis.changeRows(textarea, basicRowCount, basicHeight);
                    });
                } 
                else 
                {
                    var newHeight = (basicHeight/basicRowCount)*classThis.newRowsCount;
                    classThis.changeTextareas(textarea, function(){
                        textarea.setStyle({
                            'overflow-y': 'hidden'
                        });
                        classThis.changeRows(textarea, classThis.newRowsCount, newHeight);
                    });
                }
            });            
        });
        Form.getElements(this.mainContainer).each(function(element){
            element.observe(
                    'change',
                    function(e) {
                        new Ajax.Request(classThis.saveDataURL, {
                            method: 'post',
                            parameters: Form.serialize(classThis.mainContainer, true)
                        });
                    });
        });
    },    
    
   changeRows: function(textareaElement, newLines, newHeight, complateFunction) {
        if (textareaElement.effect) { textareaElement.effect.cancel(); }
        var complateFunction = complateFunction||new Function();
        textareaElement.effect = new Effect.Morph(textareaElement, {
            style: {
                height: newHeight + "px"
            },
            duration: 0.5,
            complateFunction:function() {
                textareaElement.setAttribute('rows', newLines);
                delete textareaElement.effect;
                complateFunction();
            }
        });
    },    
    
    changeTextareas: function(textareaElement, complateFunction) {
        if (textareaElement.effect) { textareaElement.effect.cancel(); }
        var complateFunction = complateFunction||new Function();
        if (textareaElement.scrollTop === 0) { complateFunction(); return; }
        new Effect.Tween(textareaElement, textareaElement.scrollTop, 0, {
            duration: 0.5,
            afterFinish:function() { complateFunction(); }
        }, 'scrollTop');
    }
};

OnestepcheckoutReviewNewsletter = Class.create();
OnestepcheckoutReviewNewsletter.prototype = {
    initialize: function(settings) {
        this.mainContainer = $$(settings.containerSelector).first();
        this.generalInput = $$(settings.generalInputSelector).first();
        this.segmentsContainer = $$(settings.segmentsContainerSelector).first();
        this.saveValuesUrl = settings.saveValuesUrl;
        var classThis = this;
        if (this.generalInput) {
            this.generalInput.observe(
                    'click',
                    function(e) {                        
                        if (classThis.segmentsContainer) {
                            if (classThis.generalInput.getValue()) 
                            {
                                var originalHeight = this.segmentsContainer.getStyle('height');
                                this.segmentsContainer.setStyle({'height': 'auto'});
                                var realHeight = this.segmentsContainer.getHeight();
                                this.segmentsContainer.setStyle({'height': originalHeight});
                                classThis.heightToWithEffect(realHeight);
                            } 
                            else 
                            {
                                classThis.heightToWithEffect(0);
                            }
                        }
                    });
        }        
        Form.getElements(this.mainContainer).each(function(element){
            element.observe(
                    'click',
                    function(e) {
                        new Ajax.Request(classThis.saveValuesUrl, {
                            method: 'post',
                            parameters: Form.serialize(classThis.mainContainer, true)
                        });
                    });
        });
    },
    
    heightToWithEffect: function (height) {
        var classThis = this;
        if (this.effect) { this.effect.cancel(); }
        this.effect = new Effect.Morph(this.segmentsContainer, 
        {
            style: {'height': height + "px"},
            duration: 0.5,
            afterEffect: function(){ delete classThis.effect; }
        });
    }
};

OnestepcheckoutReviewTerms = Class.create();
OnestepcheckoutReviewTerms.prototype = {
    initialize: function(settings) {        
        this.termsItems = $$(settings.itemsSelector);
        this.itemLink = settings.itemLink;
        this.itemCheckbox = settings.itemCheckbox;
        this.itemDescription = settings.itemDescription;
        this.popup = new OnestepcheckoutBox(settings.popup);
        var classThis = this;
        this.termsItems.each(function(item)
        {            
            var currentDesc = item.select(classThis.itemDescription).first();
            var currentLink = item.select(classThis.itemLink).first();
            if (currentLink && currentDesc) 
            {
                currentLink.observe('click', function(e)
                {
                    classThis.currentItem = item;
                    classThis.popup.descBoxDisplay(currentDesc.innerHTML);
                });
            }            
        });
        this.popup.buttons.accept.onClickFn = function(e)
        {
            if (classThis.currentItem) 
            {
                var currentCheckbox = classThis.currentItem.select(classThis.itemCheckbox).first();
                if (currentCheckbox) { currentCheckbox.checked = true; }
            }
        };
    }
};

OnestepcheckoutEnterpriseGiftcard = Class.create();
OnestepcheckoutEnterpriseGiftcard.prototype = {
    initialize: function(settings) {        
        this.messageContainer = $$(settings.messageContainerSelector).first();
        this.giftcardCode = $(settings.giftcardCode);        
        this.setGiftcardURL = settings.setGiftcardURL;
        this.removeGiftcardUrl = settings.removeGiftcardUrl;       
        this.complateMessageBoxClass = settings.complateMessageBoxClass;
        this.crushMessageBoxClass = settings.crushMessageBoxClass;
        this.jsCrashMsg = settings.jsCrashMsg;
        this.jsComplateMsg = settings.jsComplateMsg;        
        this.applyGiftcardButton = $$(settings.applyGiftcardButtonSelector).first();
        this.cancelGiftcardSelector = settings.cancelGiftcardSelector;       
        this.ajaxRequestId = 0;
        if (this.applyGiftcardButton) {
            var classThis = this;
            this.applyGiftcardButton.observe(
                    'click',
                    function(e) 
                    {
                        classThis.dellMessage();
                        classThis.giftcardCode.addClassName('required-entry');        
                        classThis.giftcardCode.removeClassName('required-entry');
                        if (Validation.validate(classThis.giftcardCode)) 
                        {
                            classThis.ajaxRequestId++;
                            var thisRequestId = classThis.ajaxRequestId;
                            var requestOptions = {
                                method: 'post',
                                parameters: { enterprise_giftcard_code: classThis.giftcardCode.getValue() },
                                onComplete: function(transport){ if (thisRequestId !== classThis.ajaxRequestId) { return; } 
                                    try { eval("var json = " + transport.responseText + " || {}"); }
                                    catch(e) { classThis.displayError(classThis.jsCrashMsg); return; }
                                    if (!json.success) 
                                    {
                                        var errorMessage = classThis.jsCrashMsg;
                                        if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { errorMessage = json.messages; }
                                        classThis.displayError(errorMessage);
                                    } 
                                    else 
                                    {
                                        var successMessage = classThis.jsComplateMsg;
                                        if (("messages" in json) && ("length" in json.messages) && json.messages.length > 0) { successMessage = json.messages; }
                                        OnestepcheckoutMain.messageDisplay(successMessage, classThis.complateMessageBoxClass, classThis.messageContainer);                                        
                                        var complateFunction = new Function();
                                        new Effect.Morph(classThis.messageContainer, { style: { height: classThis.messageContainer.down().getHeight() + 'px' },
                                            duration: 0.3,
                                            afterFinish: function(e){ complateFunction(); }
                                        });
                                        
                                        
                                        classThis.giftcardCode.setValue('');
                                        classThis.dellHandle();
                                    }
                                }
                            };
                            OnestepcheckoutMain.requestRUN(classThis.setGiftcardURL, requestOptions);
                        }        
                    });
        }
        this.dellHandle();
    },
    
    dellHandle: function() 
    {
        var classThis = this;
        $$(this.cancelGiftcardSelector).each(function(el) { if (el.getAttribute('href') && el.getAttribute('href').indexOf('giftcard/cart/remove/code/') !== -1)  { el.observe('click', function(e)
                {
                    e.stop();
                    classThis.dellMessage();                    
                    var requestOptions = {
                        method: 'post',
                        parameters: { enterprise_giftcard_code: el.getAttribute('href').match(/giftcard\/cart\/remove\/code\/([^\/]+)\//)[1] }
                    };
                    OnestepcheckoutMain.requestRUN(classThis.removeGiftcardUrl, requestOptions);
                }); 
            } });
    },
    
    displayError: function(message, complateFunction){
        OnestepcheckoutMain.messageDisplay(message, this.crushMessageBoxClass, this.messageContainer);        
        complateFunction = complateFunction || new Function();
        new Effect.Morph(this.messageContainer, { style: { height: this.messageContainer.down().getHeight() + 'px' },
            duration: 0.3,
            afterFinish: function(e){ complateFunction(); }
        });
    },
    
    dellMessage: function() 
    {
        if (this.messageContainer.down()) 
        {
            var classThis = this;
            new Effect.Morph(this.messageContainer, 
            {
                style: { height: 0 + 'px' },
                duration: 0.3,
                afterFinish: function(e) 
                {
                    OnestepcheckoutMain.removeMessage(classThis.messageContainer, classThis.crushMessageBoxClass);
                    OnestepcheckoutMain.removeMessage(classThis.messageContainer, classThis.complateMessageBoxClass);
                }
            });
        }
    }
};

OnestepcheckoutForm = Class.create();
OnestepcheckoutForm.prototype = {
    initialize: function(settings) {
        this.form = new VarienForm(settings.formId);
        this.cartContainer = $$(settings.cartContainerSelector).first();            
        this.shippingMethodAdviceSelector = settings.shippingMethodAdvice;
        this.shippingMethod = settings.shippingMethod;
        this.shippingMethodWrapperSelector = settings.shippingMethodWrapperSelector;
        this.shippingValidationMessage = settings.shippingValidationMessage;        
        this.paymentMethodName = settings.paymentMethodName;       
        this.paymentValidationMessage = settings.paymentValidationMessage;
         this.paymentMethodAdviceSelector = settings.paymentMethodAdvice;
        this.paymentMethodWrapperSelector = settings.paymentMethodWrapperSelector;       
        this.placeOrderUrl = settings.placeOrderUrl;
        this.successUrl = settings.successUrl;       
        this.placeOrderButton = $(settings.placeOrderButtonSelector);        
            this.pleaseWaitNotice = $$(settings.pleaseWaitNoticeSelector).first().hide(),
            this.disabledClassName = settings.disabledClassName;
        this.popup = new OnestepcheckoutBox(settings.popup);
        var classThis = this;
        Event.fire(document, 'olegnax_osc:onestepcheckout_form_init_before', {form: this});
        
        this.overlay = new Element('div');
        this.overlay.setAttribute('id', settings.overlayId);
        this.overlay.setStyle({'display':'none'});
        if (navigator.userAgent.indexOf("MSIE 8.0") == -1) { document.body.appendChild(this.overlay);}
        else 
        {            
            Event.observe(window, 'load', function(e){
                document.body.appendChild(classThis.overlay);
            });
        }        

        if (this.placeOrderButton) 
        {
            this.placeOrderButton.observe(
                    'click',
                    function() 
                    {
                        if (classThis.validate()) 
                        {
                            classThis.overlay.show();
                            classThis.placeOrderButton.addClassName(classThis.disabledClassName);
                            classThis.placeOrderButton.disabled = true;
                            
                            new Ajax.Request(classThis.placeOrderUrl, {
                                method: 'post',
                                parameters: Form.serialize(classThis.form.form, true),
                                onComplete: classThis.onComplete.bindAsEventListener(classThis)
                            });
                        }
            });
        }
        
        var originalFunction = this.cartContainer.addActionBlocksToQueueBeforeFn || Prototype.emptyFunction;
        this.cartContainer.addActionBlocksToQueueBeforeFn = function(){
            originalFunction();            
            classThis.placeOrderButton.addClassName(classThis.disabledClassName);
            classThis.placeOrderButton.disabled = true;
        };
        var originalFunction = this.cartContainer.removeActionBlocksFromQueueAfterFn || Prototype.emptyFunction;
        this.cartContainer.removeActionBlocksFromQueueAfterFn = function(response){
            originalFunction();            
            classThis.turnOnPlaceOrderButton();            
        };
        Event.fire(document, 'olegnax_osc:onestepcheckout_form_init_after', {form: this});
    },
    
    turnOnPlaceOrderButton: function() 
    {
        this.placeOrderButton.removeClassName(this.disabledClassName);
        this.placeOrderButton.disabled = false;
    },
   
    onComplete: function(transportData) 
    {
        if (transportData && transportData.responseText) 
        {
            try{ response = eval('(' + transportData.responseText + ')'); }
            catch (e) { response = {}; }
            if (!response.redirect) 
            { 
                if (response.success) { setLocation(this.successUrl); }
                else if("is_hosted_pro" in response && response.is_hosted_pro) 
                {
                    this.popup.descBoxDisplay(response.update_section.html);                    
                    this.popup.contentContainer.select('#hss-iframe').first().observe('load', function()
                    {
                        $('hss-iframe').show();
                        $('iframe-warning').show();
                    });
                }
                else
                {
                    var message = response.messages || response.message;
                    if (typeof(message) == 'object') { message = message.join("\n"); }
                    if (message) { alert(message); }
                    this.turnOnPlaceOrderButton();
                    new Effect.Morph(this.pleaseWaitNotice, { style: { 'top': '-' + this.pleaseWaitNotice.getHeight() + parseInt(this.pleaseWaitNotice.getStyle('marginTop')) + 'px'}, 'duration': 0.2 });
                    this.overlay.hide();
                } 
            }
            else { setLocation(response.redirect); }
        }
    },    
    
    validate: function() 
    {
        var validationResult = this.form.validator.validate();
        var formData = Form.serialize(this.form.form, true);        
        this.shippingMethodAdvice = $$(this.shippingMethodAdviceSelector).first();
        this.shippingMethodWrapper = $$(this.shippingMethodWrapperSelector).first();
        var shippingValidation = true;
        if (this.shippingMethodAdvice && this.shippingMethodWrapper) 
        {
            if (formData[this.shippingMethod]) 
            {
                shippingValidation = true;
                this.shippingMethodAdvice.update('').hide();
                this.shippingMethodWrapper.removeClassName('validation-failed');
            } 
            else 
            {
                shippingValidation = false;
                this.shippingMethodAdvice.update(this.shippingValidationMessage).show();
                this.shippingMethodWrapper.addClassName('validation-failed');
            }
        }        
        this.paymentMethodAdvice = $$(this.paymentMethodAdviceSelector).first();
        this.paymentMethodWrapper = $$(this.paymentMethodWrapperSelector).first();
        var paymentValidation = true;
        if (this.paymentMethodAdvice && this.paymentMethodWrapper) 
        {
            if (formData[this.paymentMethodName]) 
            {
                paymentValidation = true;
                this.paymentMethodAdvice.update('').hide();
                this.paymentMethodWrapper.removeClassName('validation-failed');
            }
            else
            {
                paymentValidation = false;
                this.paymentMethodAdvice.update(this.paymentValidationMessage).show();
                this.paymentMethodWrapper.addClassName('validation-failed'); 
            }
        }
        return (validationResult && shippingValidation && paymentValidation);
    }
};

OnestepcheckoutShipment = Class.create();
OnestepcheckoutShipment.prototype = {
    initialize: function(settings) {
        window.shippingMethod = {};
        window.shippingMethod.validator = null;        
        this.switchInputs = $$(settings.switchMethodInputsSelector);        
        var classThis = this;
        this.switchInputs.each(function(el) { if (el.checked) { classThis.currentMethod = el.value; } });
        this.switchInputs.each(function(element) { element.observe('click', function(e) 
        {
            if (classThis.currentMethod !== element.value) 
            {            
                OnestepcheckoutMain.requestRUN(settings.saveShipmentUrl, { method: 'post', parameters: Form.serialize($$(settings.containerSelector).first(), true) });
                classThis.currentMethod = element.value;
            }
        }); });
    }
};

OscShipmentEnterpriseGiftwrap = Class.create();
OscShipmentEnterpriseGiftwrap.prototype = {
    initialize: function(settings) {        
        this.addPrintedCardCheckbox = $(settings.addPrintedCardCheckbox);
        this.addGiftOptionsCheckbox = $(settings.addGiftOptionsCheckbox);
        var classThis = this;
        this.addPrintedCardUrl = settings.addPrintedCardUrl;
        if (this.addPrintedCardCheckbox) { this.addPrintedCardCheckbox.observe(
                    'change',
                    function() 
                    {        
                        var requestSettings = { method: 'post', parameters: {add_printed_card: classThis.addPrintedCardCheckbox.getValue()}, onComplete: function(transport) { classThis.ajaxCompleteFunction(transport); } };
                        OnestepcheckoutMain.requestRUN(classThis.addPrintedCardUrl, requestSettings);
                    }); }
        if (this.addGiftOptionsCheckbox) { this.addGiftOptionsCheckbox.observe(
                    'change',
                    function() 
                    {
                        if (classThis.addPrintedCardCheckbox.getValue() || classThis.isPrintedCardApplied) 
                        {
                            var requestSettings = { method: 'post', parameters: {add_printed_card: 0}, onComplete: function(transport) { classThis.ajaxCompleteFunction(transport); }};
                            OnestepcheckoutMain.requestRUN(classThis.addPrintedCardUrl, requestSettings);
                        }
                    }); }
    },
    
    ajaxCompleteFunction: function(transportData) 
    {
        try { eval("var json = " + transportData.responseText + " || {}"); }
        catch(e) { this.showError(this.jsErrorMsg); return; }
        this.isPrintedCardApplied = json.printed_card_applied;
    }
};

OnestepcheckoutAddress = Class.create();
OnestepcheckoutAddress.prototype = {
    initialize: function(settings) {
        var containersArray = this.containersArray = [];
        settings.containersSelector.each(function (selector) 
        {
            containersArray.push($$(selector).first());
        });
        this.billingForShippingCheckbox = $(settings.billingForShippingCheckbox);
        this.billingArray = {};
        this.billingArray.mainContainer = $$(settings.billing.mainContainerSelector).first();        
        this.billingArray.whenAddressChengedSelect = $$(settings.billing.whenAddressChengedSelect).first();      
        this.billingArray.addressChangedElementsIds = settings.billing.addressChangedElementsIds;
        this.billingArray.makeNewAccountInput = $(settings.billing.makeNewAccountInput);
        this.billingArray.newAddressContainerElement = $$(settings.billing.newAddressContainerElement).first();
        this.billingArray.passwordContainerElement = $$(settings.billing.passwordContainerElement).first();        

        if (settings.billing.addressCountryRegionElementsIds) {
            this.billingArray.countrySelectElement = $(settings.billing.addressCountryRegionElementsIds.countryId);
            this.billingArray.regionInputElement = $(settings.billing.addressCountryRegionElementsIds.region);
            this.billingArray.regionIdSelectElement = $(settings.billing.addressCountryRegionElementsIds.regionId);
        }
        
        this.billingArray.addressCountryRegionData = {};
        
        this.shippingArray = {};
        this.shippingArray.newAddressContainer = $$(settings.shipping.newAddressContainerSelector).first();
        this.shippingArray.mainContainer = $$(settings.shipping.mainContainerSelector).first();        
        this.shippingArray.addressChangeTriggerElementsIds = settings.shipping.addressChangeTriggerElementsIds;
        this.shippingArray.whenAddressChengedSelect = $$(settings.shipping.whenAddressChengedSelect).first();

        if (settings.shipping.addressCountryRegionElementsIds) {
            this.shippingArray.countrySelectElement = $(settings.shipping.addressCountryRegionElementsIds.countryId);
            this.shippingArray.regionInputElement = $(settings.shipping.addressCountryRegionElementsIds.region);
            this.shippingArray.regionIdSelectElement = $(settings.shipping.addressCountryRegionElementsIds.regionId);
        }
        this.shippingArray.addressCountryRegionData = {};

        this.addressChangedUrl = settings.addressChangedUrl;
        this.saveAddressUrl = settings.saveAddressUrl;
        var classThis = this;        
        
        window.billing = { newAddress: Prototype.emptyFunction };
        window.shipping = { newAddress: Prototype.emptyFunction, setSameAsBilling: Prototype.emptyFunction };        
        
        if (this.billingForShippingCheckbox) { this.billingForShippingCheckbox.observe(
                    'click',
                    function(e) {
                        if (classThis.billingForShippingCheckbox.checked) 
                        {                                         
                            classThis.hideShippingAddressContainer();                                                
                        }
                        else 
                        {                            
                            classThis.displayShippingAddressContainer();                            
                        }
                    }); }
        if (this.billingArray.makeNewAccountInput) { this.billingArray.makeNewAccountInput.observe(
                    'click',
                    function(e) 
                    {
                        if (classThis.billingArray.makeNewAccountInput.checked) 
                        {
                            classThis.billingArray.passwordContainerElement.setStyle({'display': ''});        
                            classThis.setEffect(classThis.billingArray.passwordContainerElement, OnestepcheckoutMain.elementHeight(classThis.billingArray.passwordContainerElement), 0.3, function(){ classThis.billingArray.passwordContainerElement.setStyle({'height': ''}); });
                        } 
                        else { classThis.setEffect(classThis.billingArray.passwordContainerElement, 0, 0.3, function(){ classThis.billingArray.passwordContainerElement.setStyle({'display': 'none'}); }); }
                    }); }               
        if (this.billingArray.countrySelectElement) { classThis.billingArray.countrySelectElement.observe(
                    'change',
                    function() {
                        var regionInfo = classThis.billingArray.addressCountryRegionData[classThis.billingArray.countrySelectElement.getValue()];
                        if (regionInfo) 
                        {
                            switch (regionInfo.type) 
                            {
                                case 'region': classThis.billingArray.regionInputElement.setValue(regionInfo.value); break;
                                case 'region_id': classThis.billingArray.regionIdSelectElement.setValue(regionInfo.value); break;
                            }
                        }
                    }); }
        if (this.billingArray.regionIdSelectElement) { this.billingArray.regionIdSelectElement.observe( 'change', function() { classThis.billingArray.addressCountryRegionData[classThis.billingArray.countrySelectElement.getValue()] = { 'type'  : 'region_id', 'value' : classThis.billingArray.regionIdSelectElement.getValue() }; }); }
        if (this.billingArray.regionInputElement) { this.billingArray.regionInputElement.observe( 'change', function() { classThis.billingArray.addressCountryRegionData[classThis.billingArray.countrySelectElement.getValue()] = { 'type'  : 'region', 'value' : classThis.billingArray.regionInputElement.getValue() } }); }       
        if (this.shippingArray.countrySelectElement) { this.shippingArray.countrySelectElement.observe(
                    'change',
                    function() {
                        var regionInfo = classThis.shippingArray.addressCountryRegionData[classThis.shippingArray.countrySelectElement.getValue()];
                        if (regionInfo) {
                            switch (regionInfo.type) 
                            {
                                case 'region_id': classThis.shippingArray.regionIdSelectElement.setValue(regionInfo.value); break;
                                case 'region': classThis.shippingArray.regionInputElement.setValue(regionInfo.value); break;
                            }
                        }
                    }); }
        if (this.shippingArray.regionIdSelectElement) { this.shippingArray.regionIdSelectElement.observe('change', function() { classThis.shippingArray.addressCountryRegionData[classThis.shippingArray.countrySelectElement.getValue()] = { 'type'  : 'region_id', 'value' : classThis.shippingArray.regionIdSelectElement.getValue() } }); }
        if (this.shippingArray.regionInputElement) { this.shippingArray.regionInputElement.observe( 'change', function() { classThis.shippingArray.addressCountryRegionData[classThis.shippingArray.countrySelectElement.getValue()] = { 'type'  : 'region', 'value' : classThis.shippingArray.regionInputElement.getValue() } }); }       
        if (this.billingArray.addressChangedElementsIds) 
        {
            this.billingArray.addressChangedElementsIds.each(function(elId)
            {
                var el = $(elId);
                if (el) { el.observe(
                            'change',
                            function(e) {                                
                                var validationCheck = classThis.billingArray.addressChangedElementsIds.all(function(elId)
                                {
                                    var el = $(elId);
                                    if (el) 
                                    {
                                        var cn = $w(el.className);
                                        return cn.all(function(name) 
                                        {
                                            var v = Validation.get(name);
                                            try 
                                            {
                                                if(Validation.isVisible(el) && !v.test($F(el), el)) { return false; }
                                                else { return true; }
                                            } 
                                            catch(e) { return true; }
                                        });
                                    }
                                }, classThis);
                                if (validationCheck) { classThis.addressChangedEvent(e); }
                            }); }
            });
        }
        if (this.shippingArray.addressChangeTriggerElementsIds) {
            this.shippingArray.addressChangeTriggerElementsIds.each(function(elId){
                var el = $(elId);
                if (el) { el.observe(
                            'change',
                            function(e) {                           
                            var validationCheck = classThis.shippingArray.addressChangeTriggerElementsIds.all(function(elId){
                                var el = $(elId);
                                if (el) {
                                    var cn = $w(el.className);
                                    return cn.all(function(name) {
                                        var v = Validation.get(name);
                                        try 
                                        {
                                            if(Validation.isVisible(el) && !v.test($F(el), el)) { return false; }
                                            else { return true; }
                                        }
                                        catch(e) { return true; }
                                    });
                                }
                            }, classThis);
                            if (validationCheck) { classThis.addressChangedEvent(e); }
                        }); }
            });
        }
        if (this.billingForShippingCheckbox) { this.billingForShippingCheckbox.observe('click', this.addressChangedEvent.bind(this)); }
        if (this.billingArray.whenAddressChengedSelect) { this.billingArray.whenAddressChengedSelect.observe('change', this.addressChangedEvent.bind(this)); }
        if (this.shippingArray.whenAddressChengedSelect) { this.shippingArray.whenAddressChengedSelect.observe('change', this.addressChangedEvent.bind(this)); }        
        if (this.billingArray.whenAddressChengedSelect) 
        { 
            this.billingArray.whenAddressChengedSelect.observe(
                    'change',
                    function() 
                    {
                        if (classThis.billingArray.whenAddressChengedSelect) 
                        {                            
                            if (!classThis.billingArray.whenAddressChengedSelect.value) { classThis.displayNewAddressContainer(classThis.billingArray.newAddressContainerElement); }
                            else { classThis.hideNewAddressContainer(classThis.billingArray.newAddressContainerElement); }
                        }
                    }); }
        if (this.shippingArray.whenAddressChengedSelect) { this.shippingArray.whenAddressChengedSelect.observe(
                    'change',
                    function() {
                        if (classThis.shippingArray.whenAddressChengedSelect) 
                        {            
                            if (!classThis.shippingArray.whenAddressChengedSelect.value) { classThis.displayNewAddressContainer(classThis.shippingArray.newAddressContainer); }
                            else { classThis.hideNewAddressContainer(classThis.shippingArray.newAddressContainer); }
                        }        
                    }); }
        this.containersArray.each(function(container) 
        {
            Form.getElements(container).each(function(el)
            {
                var elId = el.getAttribute('id');
                if (el !== classThis.billingForShippingCheckbox && classThis.billingArray.addressChangedElementsIds.indexOf(elId) === -1 && classThis.shippingArray.addressChangeTriggerElementsIds.indexOf(elId) === -1) 
                {
                    el.observe(
                            'change',
                            function(e)
                            {
                                var settings = $H({});
                                classThis.containersArray.each(function (currentContainer) { settings.merge(Form.serialize(currentContainer, true)); });
                                new Ajax.Request(classThis.saveAddressUrl, { method: 'post', parameters: settings });
                            });
                }                
            });
        });
        if (this.billingArray.whenAddressChengedSelect) 
        {                            
            if (!this.billingArray.whenAddressChengedSelect.value) { this.displayNewAddressContainer(this.billingArray.newAddressContainerElement); }
            else { this.hideNewAddressContainer(this.billingArray.newAddressContainerElement); }
        }
        if (this.shippingArray.whenAddressChengedSelect) 
        {            
            if (!this.shippingArray.whenAddressChengedSelect.value) { this.displayNewAddressContainer(this.shippingArray.newAddressContainer); }
            else { this.hideNewAddressContainer(this.shippingArray.newAddressContainer); }
        }
        if (this.billingForShippingCheckbox) 
        { 
            if (this.billingForShippingCheckbox.checked) {this.hideShippingAddressContainer(); }
            else { this.displayShippingAddressContainer(); } 
        }
		Event.observe(window, 'dom:loaded', function(e) 
		{			
			if (classThis.billingArray.addressChangedElementsIds) 
			{
				classThis.billingArray.addressChangedElementsIds.each(function(elId)
				{
					var el = $(elId);
					if (el) 
					{
						var validationCheck = classThis.billingArray.addressChangedElementsIds.all(function(elId)
						{							
							var el = $(elId);
							if (el) 
							{
								var cn = $w(el.className);
								return cn.all(function(name) 
								{
									var v = Validation.get(name);
									try 
									{
										if(Validation.isVisible(el) && !v.test($F(el), el)) { return false; }
										else { return true; }
									}
									catch(e) { return true; }
								});
							}
						}, classThis);
						if (validationCheck) { classThis.addressChangedEvent(e); }
					}
				});
			}
		});
    },
    
    addressChangedEvent: function(e) {
        var settings = {};        
        if (this.billingArray.mainContainer) { settings = Object.extend(settings, Form.serialize(this.billingArray.mainContainer, true)); }
        if (this.shippingArray.mainContainer) { settings = Object.extend(settings, Form.serialize(this.shippingArray.mainContainer, true)); }
        var requestSettings = { method: 'post', parameters: settings };
        OnestepcheckoutMain.requestRUN(this.addressChangedUrl, requestSettings);
    },
    
    displayShippingAddressContainer: function() 
    {
        var classThis = this;
        this.shippingArray.mainContainer.setStyle({'display': ''});        
        this.setEffect(this.shippingArray.mainContainer, OnestepcheckoutMain.elementHeight(this.shippingArray.mainContainer), 0.3, function()
        {
            classThis.shippingArray.mainContainer.setStyle({'height': ''});            
        });
    }, 
    
    hideShippingAddressContainer: function() {
        var classThis = this;        
        this.setEffect(this.shippingArray.mainContainer, 0, 0.5, function(){
            classThis.shippingArray.mainContainer.setStyle({'display': 'none'});            
        });
    },
    
    displayNewAddressContainer: function(container) 
    {
        container.setStyle({'display': ''});
        this.setEffect(container, OnestepcheckoutMain.elementHeight(container), 0.5, function(){ container.setStyle({'height': ''}); });
    },
    
    hideNewAddressContainer: function(container) {
        this.setEffect(container, 0, 0.5, function(){ container.setStyle({'display': 'none'}); });
    },
    
    setEffect: function(el, newHeight, duration, complateFunction) {
        if (el.effect) { el.effect.cancel(); }
        var complateFunctionFn = complateFunction || Prototype.emptyFunction;
        el.effect = new Effect.Morph(el, {
            style: { 'height': newHeight + 'px' },
            duration: duration,
            afterFinish: function(){ delete el.effect; complateFunctionFn(); }
        });
    }
};