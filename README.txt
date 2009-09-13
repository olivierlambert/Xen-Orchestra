### FIRST OF ALL, THIS IS A PRE ALPHA VERSION ##

So, this is NOT secure so far. Use it carefully to do some test
and reports bugs. 

Go to :
http://project.xen-orchestra.com

To report bug, download latest version etc.

Thanks for testing and maybe contributing.

### HOW TO CONFIGURE XEN DOM0 FOR CALL THE API ###
# Ultra small documentation, please read the comment in 
# xen configuration file

- edit :
/etc/xen/xen-config.sxp

- to open a port for remote calls, set :
(xen-api-server ((9363 unix)))

/!\ WARNING : this is a example without protections.

Carefully read :
#   (xen-api-server ((9363 pam '^localhost$ example\\.com$')
#                    (unix none)))
#
# Optionally, the TCP Xen-API server can use SSL by specifying the private
# key and certificate location:
#
#                    (9367 pam '' /etc/xen/xen-api.key /etc/xen/xen-api.crt)
