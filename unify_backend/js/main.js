
jQuery(document).ready(function(){

    var $ = jQuery;
    var container = $('#admin_customize');
    
    var options = {
        
        init: function(){
        
            this.injectToggleOptions();
            this.bindStates();
        },
        
        // Toggle children within options lists 
        
        injectToggleOptions: function(){
    
            container.find('li').each(function(){
                
                var self     = $(this);
                var children = self.find('ul').hide();
                
                if(children.length > 0){
                    
                    self.prepend('<span class="ac_toggle"><a href="javascript:void(0)">&raquo</a>&nbsp;</span>')
                        .on('click', 'a', function(){
                        
                            self.children('ul').toggle();
                    });
                }
                else{
                    
                    self.prepend('<span class="ac_toggle">&raquo&nbsp;</span>');
                }
            });
        },
        
        // Three-state-checkboxes
        //
        // Based on:
        // @see http://css-tricks.com/indeterminate-checkboxes/
        
        bindStates: function(){
            
            // State on event
            
            container.on('change', 'input[type="checkbox"]', function(event){
                
                var self = $(this);
                
                if(!event.autoTriggered){
                    
                    // Set self and all children to current self state
                    
                    self.parent().find('input[type="checkbox"]').prop({
                        
                        indeterminate: false,
                        checked: self.prop('checked')
                    });

                    // Trigger event one level up
                    
                    var parent = self.parents('li').eq(1).find('input[type="checkbox"]').first();
                    
                    if(parent.length > 0){
                        
                        parent.trigger({type: 'change', autoTriggered: true});
                    }
                }
                else{
                    
                    // Determine self state by children's state
                    
                    var children  = self.parent().children().find('input[type="checkbox"]');
                    
                    if(children.length > 0){
                        
                        var checked = children.filter(':checked').length;
                        var state   = { 
                            
                            indeterminate: false, // Default state: all children are unchecked 
                            checked: false 
                        };
                        
                        if(checked > 0){ // All or some children are checked
                            
                            checked == children.length ? (state.checked = true) : (state.indeterminate = true); 
                        }
                        
                        self.prop(state);
                    }
                }
                
            });
            
            // Initial state
            
            container.find('input[type="checkbox"]').not(':checked').trigger({type: 'change', autoTriggered: true});
        },
    };
    
    options.init();
});