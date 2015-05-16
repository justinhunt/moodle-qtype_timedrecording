/**
 * Javascript for replacing numeric grades with labeled grades.
 *
 * @copyright &copy; 2012 Justin Hunt
 * @author poodllsupport@gmail.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package timedrecording
 */

M.qtype_timedrecording = {

	Y: null,

	init: function(Y,opts){
		this.Y = Y;
		
	},
	
	callback: function(args){
		console.log(args);
			switch(args[1]){
				case 'donext':
					var f=document.getElementById("responseform");
					f.next.click();	
					break;
					
				case 'filesubmitted':
				default:
				//record the url on the html page,							
					var filenamecontrol = document.getElementById(args[3]);
					if(filenamecontrol==null){ filenamecontrol = parent.document.getElementById(args[3]);} 			
					if(filenamecontrol){
						filenamecontrol.value = args[2];
					}
			}
	},

	// Replace the standard grading textbox with a dropdown of options
	init_dropdown: function(Y, vars) {
		//YAHOO.util.Event.addListener(window,'load','initList'); 
		//return;
		var myNode = Y.one(document.body);
		//alert("one");
		//var elements = Dom.getElementsByClassName('felement ftext', 'div');
		var elements = myNode.all('.que .timedrecording .manualgraded .complete', 'div');
		//var elements = Dom.getElementsByClassName('timedrecording', 'div');

		if(this.isArray(elements)){
			for (i in elements){
				
				var tbox = elements[i].all('felement ftext','div');
				//var tbox = Dom.getElementsByClassName('ftext','div',elements[i]);
				if(this.isArray(tbox)){
					for (j in tbox){
						//this next line screws up the getChild stuff if called later, because the child will point to nothing,
						// so has to be called prior to that
						//thats why we don't try to deduce the max score here. If it changes we
						//should just change it here
						tbox[j].innerHTML = tbox[j].innerHTML.replace('out of 11.00', '');
					
						var oldChild = tbox[j].one('*');
						if(oldChild==null || oldChild.tagName != 'INPUT'){return;}
						var oldValue = oldChild.value;
						//alert(oldValue);
						var maxScoreChild = oldChild.next();
						if(!maxScoreChild){return;}
						var maxscore = maxScoreChild.value;
						
						var select = document.createElement("select");
						select.add(this.makeOption("choosegrade",0,oldValue));
						select.add(this.makeOption("novicelow",1,oldValue));
						select.add(this.makeOption("novicemid",2,oldValue));
						select.add(this.makeOption("novicehigh",maxscore,3,oldValue));
						select.add(this.makeOption("intermediatelow",4,oldValue));
						select.add(this.makeOption("intermediatemid",5,oldValue));
						select.add(this.makeOption("intermediatehigh",6,oldValue));
						select.add(this.makeOption("advancedlow",7,oldValue));
						select.add(this.makeOption("advancedmid",8,oldValue));
						select.add(this.makeOption("advancedhigh",9,oldValue));
						select.add(this.makeOption("superior",10,oldValue));
						select.add(this.makeOption("distinguished",11,oldValue));
			

						//select.innerHTML = makeOption("gofigure",10,0,4) + makeOption("mofigure",10,1,5) ;
						/*
						select.innerHTML = makeOption("Please choose a grade",maxscore,0,oldValue) +
											makeOption("Novice-Low",maxscore,1,oldValue) +
											makeOption("Novice-Mid",maxscore,2,oldValue) +
											makeOption("Novice-High",maxscore,3,oldValue) +
											makeOption("Intermediate-Low",maxscore,4,oldValue) +
											makeOption("Intermediate-Mid",maxscore,5,oldValue) +
											makeOption("Intermediate-High",maxscore,6,oldValue) +
											makeOption("Advanced-Low",maxscore,7,oldValue) +
											makeOption("Advanced-Mid",maxscore,8,oldValue) +
											makeOption("Advanced-High",maxscore,9,oldValue) +
											makeOption("Superior",maxscore,10,oldValue) +
											makeOption("Distinguished",maxscore,11,oldValue);
						*/
						select.id = oldChild.getAttribute('name');
						select.name = oldChild.getAttribute('name');
						ySelect = Y.create(select);
						oldChild.insert(ySelect,'before');
						//Dom.insertBefore(select,oldChild);
						tbox[j].removeChild(oldChild);
						
					}
				}
				
			}
		}
	},



	isArray: function(obj) {
		return obj && obj.constructor == Array;
	},

	makeOption: function(gradelabel,gradevalue,selvalue) {

		var oOption = document.createElement("OPTION");
		//here we get the localised string for the grade label
	   oOption.text=M.util.get_string(gradelabel,'qtype_timedrecording');
	   
	   oOption.value=gradevalue;
	  oOption.selected=(gradevalue==selvalue);
		return oOption;

	},



	round_number: function(num, dec) {
		return Math.round(num * Math.pow(10, dec)) / Math.pow(10, dec);
	}

}



