var theUploadFrame = null;

function MyHomePageMetaGroupModel()
{
	var self = this;
	var d = new Date();
	self.id = 0;
	self.title = '';
	self.description = '';
	self.iconId = 0;
	self.iconUrl = '';
	self.url = '';
	self.dataJson = '';
	self.theID = d.getTime() - 1494088525598;

	self.init = function(obj)
	{
		self.id 				= ko.observable(obj.id);
		self.title 				= ko.observable(obj.title);
		self.description 		= ko.observable(convertBtagString(obj.description));
		self.iconId 			= ko.observable(obj.iconId);
		self.iconUrl 			= ko.observable(obj.iconUrl);
		self.url 				= ko.observable(obj.url);
		self.dataJson 			= ko.computed(function(){
			var id1 			= self.id()?self.id():"0";
			var title1 			= self.title()?self.title():"";
			var description1 	= self.description()?self.description():"";
			var iconId1 		= self.iconId()?self.iconId():"0";
			var iconUrl1		= self.iconUrl()?self.iconUrl():"";
			var url1			= self.url()?self.url():"";
			var json = {"id":id1, "title":title1, "description":description1, "iconId":iconId1, "iconUrl":iconUrl1, "url":url1};
			return JSON.stringify(json);
		}, self);
	}

	self.makeNew = function()
	{
		self.id 			= ko.observable(self.theID - 1);
		self.title 			= ko.observable();
		self.description 	= ko.observable();
		self.iconId 		= ko.observable();
		self.iconUrl 		= ko.observable();
		self.url 			= ko.observable();
		self.dataJson 		= ko.computed(function(){
			var id1 			= self.id()?self.id():"0";
			var title1 			= self.title()?self.title():"";
			var description1 	= self.description()?self.description():"";
			var iconId1 		= self.iconId()?self.iconId():"0";
			var iconUrl1		= self.iconUrl()?self.iconUrl():"";
			var url1			= self.url()?self.url():"";
			var json = {"id":id1, "title":title1, "description":description1, "iconId":iconId1, "iconUrl":iconUrl1, "url":url1};
			return JSON.stringify(json);
		}, self);
	}
}

function MyHomePageMetaGroupController()
{
	var self = this;
	self.homePageMetaGroup = ko.observableArray();
	self.theID = 0;
	self.tmpArr = null;
	self.homeMetaData = '';
	self.currentItem = null;

	self.addEntry = function()
	{
		var obj = new MyHomePageMetaGroupModel();
		obj.makeNew();
		self.homePageMetaGroup.push(obj);
	}

	self.removeEntry = function()
	{
		self.homePageMetaGroup.remove(this);
	}

	self.init = function(jsonStr)
	{
		var input = JSON.parse(jsonStr);
		if(input.data.length > 0)
		{
			input.data.forEach(self.makeNew);
		}

		self.homeMetaData = ko.computed(function(){
			if(self.homePageMetaGroup().length>0)
			{
				self.tmpArr = [];
				self.homePageMetaGroup().forEach(self.parseToJson);
				var tmp = {"data":self.tmpArr};
				var json = JSON.stringify(tmp);
				tmp = '';
				self.tmpArr = null;
				return json;
			}
			else return '{"data":[]}';
			
		},self);

	}

	self.makeNew = function(item, index)
	{
		var obj = new MyHomePageMetaGroupModel();
		item = JSON.parse(item);
		obj.init(item);
		self.homePageMetaGroup.push(obj);
	}

	self.updateData = function()
	{
		self.homePageMetaGroup().forEach(self.parseToJson);
	}

	self.parseToJson = function(item, index)
	{
		self.tmpArr.push(item.dataJson());
	}

	self.addMediaImage = function(ele)
	{
		self.currentItem = ele;
		var id_sel 			= '#item_'+ele.id();
		console.log(id_sel);
		var my 				= this;
		my.ele				= ele;
		my.upload_sel 		= id_sel + ' .add-new-icon';
		my.iconUrl_sel 		= id_sel + ' .iconUrl';
		my.iconId_sel 		= id_sel + ' .iconId';
		my.upload 			= jQuery(my.upload_sel);
		my.iconUrlObj 		= jQuery(my.iconUrl_sel);
		my.iconIdObj 		= jQuery(my.iconId_sel);

		// If the media frame already exists, reopen it.
        if ( theUploadFrame ) {
            theUploadFrame.open();
            return;
        }

        // Create a new media frame
        theUploadFrame = wp.media({
            title: 'Select or Upload Media Of Your Chosen Persuasion',
            button: {
                text: 'Use this media'
            },
            multiple: 'false'  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        theUploadFrame.on( 'select', function() {
            // Get media attachment details from the frame state
            var n = theUploadFrame.state().get('selection').models.length;
            if(n>0)
            {
            	var attachment = theUploadFrame.state().get('selection').models[0].attributes;
            	self.currentItem.iconId(attachment.id);
            	self.currentItem.iconUrl(attachment.url);
            }

        });
        

        // Finally, open the modal on click
        theUploadFrame.open();
	}

	self.removeMediaImage = function(ele)
	{
		ele.iconId(0);
		ele.iconUrl("");
	}
}

jQuery(document).ready(function($) { 
	if($('#homePageMetaGroup_value').length > 0) {
		myHomeMeta = new MyHomePageMetaGroupController();
    	var json = $('#homePageMetaGroup_value').val();
    	myHomeMeta.init(json);
    	ko.applyBindings(myHomeMeta, document.getElementById("homePageMetaGroup_wrapper_id"));
	}
	
});