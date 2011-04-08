/**
*   Jquery validation for phone numbers
*   @author kinshuk <kinshuk1989@gmail.com>  
*
*/

// Declaring required variables
var digits = "0123456789";
// non-digit characters which are allowed in phone numbers
var phoneNumberDelimiters = "()- ";
// characters which are allowed in international phone numbers
// (a leading + is OK)
var validWorldPhoneChars = phoneNumberDelimiters + "+";
// Minimum no of digits in an international phone no.
var minDigitsInIPhoneNumber = 10;

function isInteger(s)
{   var i;
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9"))) return false;
    }
    // All characters are numbers.
    return true;
}
function trim(s)
{   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not a whitespace, append to returnString.
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (c != " ") returnString += c;
    }
    return returnString;
}
function stripCharsInBag(s, bag)
{   var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++)
    {   
        // Check that current character isn't whitespace.
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
} 

jQuery.validator.addMethod("phone", function(phone_number, element) {      
        var bracket=3
        strPhone= trim(phone_number)
        if(strPhone.indexOf("+")>1) return false
        if(strPhone.indexOf("-")!=-1)bracket=bracket+1
        if(strPhone.indexOf("(")!=-1 && strPhone.indexOf("(")>bracket)return false
        var brchr=strPhone.indexOf("(")
        if(strPhone.indexOf("(")!=-1 && strPhone.charAt(brchr+2)!=")")return false
        if(strPhone.indexOf("(")==-1 && strPhone.indexOf(")")!=-1)return false
        s=stripCharsInBag(strPhone,validWorldPhoneChars);
        return (isInteger(s) && s.length >= minDigitsInIPhoneNumber);    
}, "Please specify a valid phone number");
