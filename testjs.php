<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script>
function validate(){
if($('[name="userGender"]:checked').length >0){
	alert('valid'+$('[name="userGender"]:checked').val())
}
else{
	alert('not')
}
var emailstr = $('#email').val();
var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
    if (filter.test(emailstr)) {
        alert('pass')
    }
    else {
        alert('fail')
    }
	
}
</script>
</head>
<body>
<form>
<input type="email" name="email" id="email">
<input type="radio" name="userGender" value="male"> <input type="radio" name="userGender" value="female">
<input type="button" onclick="validate()">
</form>

</body>

</html>