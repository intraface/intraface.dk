
function parseUrlIdentifier(string) {
    
    // space replaced with - 
    string = string.replace(/\s/g, '-'); 
    
    // to lower string
    string = string.toLowerCase();
    
    // danish characters
    string = string.replace(/ø/g, 'oe'); 
    string = string.replace(/æ/g, 'ae'); 
    string = string.replace(/å/g, 'aa');
    
    var return_string = '';
    var pattern = /\w|-|_/g;
    
    while((result = pattern.exec(string)) != null) {
        return_string = return_string + result[0];
    }
    return return_string;
}