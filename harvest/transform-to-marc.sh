#!/bin/bash
for file in /usr/local/vufind/local/harvest/Sobek/*.xml
do
  yaz-marcdump -i marcxml -o marc "$file" -v > "${file/.xml/.mrc}"
done
cat /usr/local/vufind/local/harvest/Sobek/*.mrc > /usr/local/vufind/local/harvest/Sobek/sobek.txt
rm -Rf /usr/local/vufind/local/harvest/Sobek/*.mrc
#rm -Rf /usr/local/vufind/local/harvest/Sobek/*.xml
mv /usr/local/vufind/local/harvest/Sobek/sobek.txt /usr/local/vufind/local/harvest/Sobek/sobek.mrc

