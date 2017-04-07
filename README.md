# compresspic
使用tinyPNG的API全自动压图
在tinyPng下新建pic文件夹和pic_out文件夹
将包含需要压缩的图片的目录拷贝至pic目录下
打开compress.php文件，在$apikey中填入所有的从https://tinypng.com/developers上申请到的key
（每个key可使用500次，超出后自动切换key，如若不够用会弹出"Please Enter a new APIKey: ",填入新的APIKey就行了）
运行php compress.php压图开始
