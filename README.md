# Vanitas

Vanitas is a project in pure PHP using only simple functions and internal data.
Its goal is to give you various metrics about peoples and bots acceding your webiste.

## Why choose Vanitas

- Incredibly simple to install and use. You upload it on your server and add a simple
 line of code in your code source.
- Use absolutely no external resource (no curl request, no external database, no API,
 ...) Everything is kept on your application. Absolutly no data gets out. 
- No database. All data are stored in text files. The results are put in a .csv file
that you would eventually read and parse quite easily.
- Fast. So fast ! For each visitor coming on each of your page, their metrics are
detected and stored in a blink (less than 0.10 sec)
- Compatible with PHP 5 and PHP 7 and all their updates. Do not worry about that. :)
- Works with IPV4 and IPV6 although IPV6 is way less intresincally accurate.

## Why not choose Vanitas

- You want very accurate geographic data ? Sorry, the application is only precise up
to countries. I lack of more specific, efficient and reliable data. 

## The date recorded

The data that would be available to you are :

- Date : 2019-04-07
- Hour : 10:31:24
- IP adress : 127.0.0.1
- Is it IPV4 ? : 1
- Is it IPV6 ? : 0
- Visited page : http://www.example.com/
- Coming from url : http://www.olderExample.com/ 
- Country name : France
- Country tag (2 letters) : FR
- Country tag (3 letters) : FRA
- User-agent : "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:66.0) Gecko/20100101 Firefox/66.0"
- Is it a bot ? : 1
- Browser name : Firefox
- Browser version : 66.0
- Browser engine : Gecko,
- Processor architecture (is 64 bits ?) : 1
- Using a mobile device : 1
- OS family : Linux,
- OS version : unknown
- OS name : unknown


## Tools/library used

To gather IP informations, I got the set from [geo-ip](http://software77.net/geo-ip/).
Would you please consider make them a donation (even a tiny one) because they truly
deserve it. License [donationware](http://software77.net/geo-ip/?license).

To parse the user-agent, I used Wolfcast's great work :
[BrowserDetection](https://github.com/Wolfcast/BrowserDetection). License
[GNU LGPL](GNU Lesser General Public License)

## How to use

## Next updates

- Trying [Maxmind](https://dev.maxmind.com/geoip/geoip2/geolite2/) database if it is
more efficient.
- Add customizable interactive graphs that would be easily displayed on your website. 

## License

This program is free software; you can redistribute it and/or modify it under the
terms of the GNU Lesser General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later version
(if any).

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details at: 
http://www.gnu.org/licenses/lgpl.html