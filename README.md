# find-my-luggage
It creates a QR with your personal informations to attach on items like luggages or something similar. 

It also allows you to hide (encrypt) your personal informations unless you explicitly tell it not to, in order to protect your privacy against improper Qr scans.



## Mode 1: Plain text
A plain text Qr code will be generated. Anyone scanning it can visualize the data.

## Mode 2: Encrypted text
The Qr needs to be unlocked the moment you realize you've lost the item in order to visualize the infos it carries. The link will be sent in your e-mail 
inbox and will be shown in the popup.

The Qr will carry encrypted text, the database will hold the key to decrypt it; an attack on the database will not show anyone's data. 

Hence, to use it in this mode, you will need a webserver and a database.

## Gallery
### Homepage

<img src="/images/example1.png"/>

### Mode 1

<img src="/images/example2.png"/>
<img src="/images/example3.jpeg"/>

### Mode 2

<img src="/images/example4.png"/>
<img src="/images/example5.png"/>
<img src="/images/example6.png"/>

