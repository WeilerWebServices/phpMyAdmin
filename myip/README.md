# MyIP
Collection of PHP scripts for discovering and managing IP addresses

If you run a server at your home, you know the problem: you're out of the house, when you realize you need to fix something on the server in your bedroom. You pull out your laptop and try to SSH to your home network only to realize that the Internet Service Provider has changed your public IP address again.

Normally you can just wait until you get home to fix it, but maybe it's something that needs to be fixed *now*, or you're on a trip and it's going to be too long until you can connect to the network again. Maybe you're a CS student in college with a server running at your parents' house.

## Determining your public-facing IP address

The simplest part of this project is a PHP page that simply displays to the user their public-facing IP address. It's useful for scripting projects that require this piece of knowledge and for someone who needs the information just once.

## Saving a list of your IP addresses

The page mentioned above doesn't help any when you're away from the network and can't look up that public IP address. You could set up a script that pulls the address from the page above and sends it via XMPP or some other chat network, but that is such a hassle to set up. Instead, you can send a quick HTTP request to a web server saying "this is my machine, please keep track of its current IP address for me". Then, later, you can authenticate with the server and view the last-updated IP address of each machine. This request is possible on the commandline with `curl`, so it's easy to set up in a cron job to update regularly.

## Hosts file?

Something I hope to implement soon is the ability to authenticate with the server and view your machine/ip list in hosts file format so that compatible hosts file editing software can take a URL using HTTP authentication and automatically update the hosts file with the latest ip address. Thus, after the initial setup, there's nothing more to worry about.