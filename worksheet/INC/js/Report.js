//Check for valid Date
function ValidateDate(dat)
{
var arr;
var tmp;

	tmp=dat.value;
	while(tmp.indexOf(" ")>=0) tmp = tmp.replace(" ","");
	while(tmp.indexOf(".")>=0) tmp = tmp.replace(".","-");
	while(tmp.indexOf(":")>=0) tmp = tmp.replace(":","-");
	while(tmp.indexOf("/")>=0) tmp = tmp.replace("/","-");
	if (tmp.indexOf("-")>=0)
	{
		arr = tmp.split("-");
		if (arr.length!=3) return false;
		if ((arr[0]<1)||(arr[0]>31)) return false;
		if ((arr[1]<1)||(arr[1]>12)) return false;
		if (arr[2]<100) arr[2]="20"+arr[2];
		if ((arr[2]<1995)||(arr[2]>2020)) return false;
		if(arr[0].length==1) dat.value="0"+arr[0]; else dat.value=arr[0];
		if(arr[1].length==1) dat.value=dat.value+"-0"+arr[1]; else dat.value=dat.value+"-"+arr[1];
		dat.value=dat.value+"-"+arr[2];
		return true;
	}
	else return false;
}

// It suppose that dat1 è dat2 are already checked by ValidateDate
function Compare2Date(dat1,dat2)
{
var arr1;
var arr2;
var val1;
var val2;

	tmp1=dat1;
	while(tmp1.indexOf(" ")>=0) tmp1 = tmp1.replace(" ","");
	while(tmp1.indexOf(".")>=0) tmp1 = tmp1.replace(".","-");
	while(tmp1.indexOf(":")>=0) tmp1 = tmp1.replace(":","-");
	while(tmp1.indexOf("/")>=0) tmp1 = tmp1.replace("/","-");
	tmp2=dat2;
	while(tmp2.indexOf(" ")>=0) tmp2 = tmp2.replace(" ","");
	while(tmp2.indexOf(".")>=0) tmp2 = tmp2.replace(".","-");
	while(tmp2.indexOf(":")>=0) tmp2 = tmp2.replace(":","-");
	while(tmp2.indexOf("/")>=0) tmp2 = tmp2.replace("/","-");

	arr1 = tmp1.split("-");
	if (arr1.length!=3) return false;
	val1 = new Date(arr1[2],arr1[1]-1,arr1[0]);

	arr2 = tmp2.split("-");
	if (arr2.length!=3) return false;
	val2 = new Date(arr2[2],arr2[1]-1,arr2[0]);

	if (val1<val2) return -1;
	if (val1==val2) return 0;
	if (val1>val2) return 1;
}

//Check for valid Month
function ValidateMonth(dat)
{
var arr;
var tmp;

	tmp=dat.value;
	while(tmp.indexOf(" ")>=0) tmp = tmp.replace(" ","");
	while(tmp.indexOf(".")>=0) tmp = tmp.replace(".","-");
	while(tmp.indexOf(":")>=0) tmp = tmp.replace(":","-");
	while(tmp.indexOf("/")>=0) tmp = tmp.replace("/","-");
	if (tmp.indexOf("-")>=0)
	{
		arr = tmp.split("-");
		if (arr.length!=2) return false;
		if ((arr[0]<1)||(arr[0]>12)) return false;
		if (arr[1]<100) arr[1]="20"+arr[1];
		if ((arr[1]<1995)||(arr[1]>2020)) return false;
		dat.value=arr[0]+"-"+arr[1];
		return true;
	}
	else return false;
}

function ValidKarta(top)
{
	if(top.rab_1.value<1)
	{
	  alert('Missing employee');
	  return false;
	}
	if(!ValidateDate(top.beg_1))
	{
		alert('Invalid start date');
		return false;
	}
	if(!ValidateDate(top.end_1))
	{
		alert('Invalid end date');
		return false;
	}
	if(Compare2Date(top.beg_1.value,top.end_1.value)>0)
	{
	  alert('End date should be later than start date');
	  return false;
	}
	return true;
}

function ValidRab(top)
{
	if(!ValidateDate(top.beg_2))
	{
		alert('Invalid start date');
		return false;
	}
	if(!ValidateDate(top.end_2))
	{
		alert('Invalid end date');
		return false;
	}
	if(Compare2Date(top.beg_2.value,top.end_2.value)>0)
	{
	  alert('End date should be later than start date');
	  return false;
	}
	return true;
}

function ValidOper(top)
{
	if(!ValidateDate(top.beg_3))
	{
		alert('Invalid start date');
		return false;
	}
	if(!ValidateDate(top.end_3))
	{
		alert('Invalid end date');
		return false;
	}
	if(Compare2Date(top.beg_3.value,top.end_3.value)>0)
	{
	  alert('End date should be later than start date');
	  return false;
	}
	return true;
}

function ValidNova(top)
{
	if(!ValidateDate(top.beg_4))
	{
		alert('Invalida start date');
		return false;
	}
	if(!ValidateDate(top.end_4))
	{
		alert('Invalid end date');
		return false;
	}
	if(Compare2Date(top.beg_4.value,top.end_4.value)>0)
	{
	  alert('End date should be later than start date');
	  return false;
	}
	return true;
}

function ValidZap(top)
{
	if(!ValidateMonth(top.mesec))
	{
		alert('Invalid month');
		return false;
	}
	return true;
}
