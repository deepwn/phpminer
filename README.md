# phpminer
A pure PHP miner for CryptoNight


Based on https://github.com/shift-reality/php-crypto ,Thanks to @Bogdan

<h2>it's not finished yet</h2>

The project's almost done, Incredibly cryptonight() produce 27 seconds a hash.So the hashrate is 0.03H/s.
I have known that the bottleheck is that php doesn't fit to calculate and mem access so frequently.

I hope that this project can achieve at lease 1H/s.
So there are some possible solutions:
①Waiting for Just-In-Time of PHP8.
②make an extension of cryptonight algo.

and I will keep moving till this project reach the expectation.
