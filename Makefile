exec:
	docker run --rm -it \
		-v $(CURDIR):/app \
		--network dev \
		toroia/phalcon-cli bash

test:
	docker run --rm -it \
		-v $(CURDIR):/app \
		--network dev \
		toroia/phalcon-cli php -d display_errors=On -d error_reporting=E_ALL vendor/bin/codecept run
