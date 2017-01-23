</div>


<?php foreach ($this->scripts as $filename) {
	if (file_exists("./public/js/$filename.js"))
		printf(PHP_EOL . '<script type="text/javascript" src="%s"></script>', "/public/js/$filename.js");
} ?>

</body>
</html>