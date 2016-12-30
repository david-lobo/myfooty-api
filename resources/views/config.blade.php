<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	 @foreach ($params as $key => $value)
	<key>{{ $key }}</key>
	<string>{{ $value }}</string>
	@endforeach
</dict>
</plist>