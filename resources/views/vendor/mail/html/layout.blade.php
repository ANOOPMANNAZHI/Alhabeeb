<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
    <style>
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
            }

            .footer {
                width: 100% !important;
            }
        }

        @media only screen and (max-width: 500px) {
            .button {
                width: 100% !important;
            }
        }
    </style>

   <table width="100%" cellpadding="0" cellspacing="0" border="0" >
    <tr>        
        <td>  
        <div style="box-shadow: 0px 0px 5px rgba(0,0,0,.3); margin: 0 auto; font-family: arial;">          
                 {{ $header ?? '' }}

                <div style="padding: 30px 10px; text-align: center; font-size: 14px; color:#005aab;">               
                                            
                 {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                </div>                  
                
                {{ $footer ?? '' }}
            </div>
           
        </td>
        
    </tr>
    <tr>
        <td>&nbsp;</td>       
    </tr>
</table>
 
</body>
</html>
