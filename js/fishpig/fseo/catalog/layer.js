/**
 *
 */
 
var FishPig = FishPig || {}

FishPig.FSeo = FishPig.FSeo || {};
FishPig.FSeo.Catalog = FishPig.FSeo.Catalog || {}

FishPig.FSeo.Catalog.Layer = Class.create({
	initialize: function(wrapperId) {
		this.wrapper = $(wrapperId);
		this._activeUrls = new Array();
	},
	run: function() {
		this.wrapper.select('ol li').each(function(elem) {
			var a = elem.down('a');
			var isChecked = null;

			if (this._activeUrls.indexOf(a.href) >= 0) {
				elem.addClassName('active');
				isChecked = 'checked';
				
				elem.update(a);
			}

			a.insert({
				top: new Element('input', {type: 'checkbox', class: 'control', checked: isChecked, style: 'margin-right: 4px;margin-top:-2.5px;'})
			});
			
			a.observe('click', function(event) {
				var clickedElem = Event.element(event);
				
				var i = a.select('input').first();
				
				i.writeAttribute('checked', i.readAttribute('checked') ? null : 'checked');
				i.writeAttribute('disabled', 'disabled');
				
				if (clickedElem.nodeName === 'INPUT') {
					window.location.href = a.readAttribute('href');	
				}
			});
			

		}.bind(this));
	},
	addActiveUrl: function(url) {
		this._activeUrls.push(url);
		
		return this;
	}
});
