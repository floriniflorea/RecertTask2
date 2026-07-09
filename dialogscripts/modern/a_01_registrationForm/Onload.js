function Onload()
{
    
    /*check if read-only */
    if (jr_is_readonly('ReadOnlyMartor'))
    {
        jr_notify_info('ReadOnlyMode',5);
       
    }
    if(window.location.href.indexOf("jrsimulation") > -1)
    {
        jr_notify_info('I am in the simulator ' + window.location.href.indexOf("jrsimulation"),5);
       
    }else{
        alert('I am NOT in the simulator ' + window.location.href.indexOf("jrsimulation"));
    }
   
}