/* global pffrm */

;(function($) {

	'use strict';

	// Global settings access.
	var s;

	// Admin object.
	var PFtoFrm = {

		init: function() {
			s = {};

			jQuery(document).on('submit','#frm_form_pf_importer', function( event ) {

				event.preventDefault();

				// Check to confirm user as selected a form.
				var checkedBoxes = $( '#frm_form_pf_importer input:checked' );
				if ( checkedBoxes.length ) {

					var ids = [];
					checkedBoxes.each( function ( i ) {
						ids[i] = this.value;
					});

					// Begin the import process.
					PFtoFrm.importForms( ids );
				}
			});
		},

		/**
		* Begins the process of importing the forms.
		*/
		importForms: function( forms ) {

			var $processSettings = $( '#frm-importer-process' );

			// Display total number of forms we have to import.
			$processSettings.find( '.form-total' ).text( forms.length );
			$processSettings.find( '.form-current' ).text( '1' );

			// Hide the form select section.
			$( '#frm_form_pf_importer' ).hide();

			// Show processing status.
			$processSettings.show();
			$processSettings.find( '.process-completed' ).hide();

			// Create global import queue.
			s.importQueue = forms;
			s.imported    = 0;

			// Import the first form in the queue.
			PFtoFrm.importForm();
		},

		/**
		* Imports a single form from the import queue.
		*/
		importForm: function() {
			var $processSettings = $( '#frm-importer-process' ),
				formID           = s.importQueue[0],
				provider         = $('input[name="slug"]').val(),
				data             = {
					action:  'frm_import_' + provider,
					form_id: formID,
					nonce:   pffrm.nonce
				};

			// Trigger AJAX import for this form.
			$.post( pffrm.ajax_url, data, function( res ) {

				if ( res.success ){
					var statusUpdate;

					if ( res.data.error ) {
						statusUpdate = '<p>' + res.data.name + ': ' + res.data.msg + '</p>';
					} else {
						statusUpdate = '<p>Imported <a href="' + res.data.link + '" target="_blank">' + res.data.name + '</a></p>';
					}

					$processSettings.find( '.status' ).prepend( statusUpdate );
					$processSettings.find( '.status' ).show();

					// Remove this form ID from the queue.
					s.importQueue = jQuery.grep(s.importQueue, function(value) {
					  return value != formID;
					});
					s.imported++;

					if ( s.importQueue.length === 0 ) {
						$processSettings.find( '.process-count' ).hide();
						$processSettings.find( '.forms-completed' ).text( s.imported );
						$processSettings.find( '.process-completed' ).show();
					} else {
						// Import next form in the queue.
						$processSettings.find( '.form-current' ).text( s.imported+1 );
						PFtoFrm.importForm();
					}
				}
			});
		}
	};
	PFtoFrm.init();

	window.PFtoFrm = PFtoFrm;

})( jQuery );