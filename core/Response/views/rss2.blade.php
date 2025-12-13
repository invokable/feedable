<rss version="2.0"
     xmlns:content="http://purl.org/rss/1.0/modules/content/"
     xmlns:dc="http://purl.org/dc/elements/1.1/"
     xmlns:media="http://search.yahoo.com/mrss/"
     xmlns:atom="http://www.w3.org/2005/Atom"
>
    <channel>
        <title>{{ $title }}</title>
        <link>{{ $link }}</link>
        <atom:link href="{{ $link }}" rel="self" type="application/rss+xml"/>
        <description>{{ $description }}</description>
        <generator>Feedable</generator>
        @if(filled($language))
            <language>{{ $language }}</language>
        @endif
        @if(filled($image))
            <image>
                <url>{{ $image }}</url>
                <title>{{ $title }}</title>
                <link>{{ $link }}</link>
            </image>
        @endif
        <ttl>{{ $ttl }}</ttl>
        @foreach($items as $item)
            <item>
                <title>{{ $item['title'] }}</title>
                <link>{{ $item['link'] }}</link>
                <guid isPermaLink="false">{{ $item['link'] }}</guid>
                @if(isset($item['pubDate']) && filled($item['pubDate']))
                    <pubDate>{{ $item['pubDate'] }}</pubDate>
                @endif
                <description><![CDATA[{!! $item['description'] !!}]]></description>
                @if(isset($item['content']) && filled($item['content']))
                    <content:encoded><![CDATA[{!! $item['content'] !!}]]></content:encoded>
                @endif
                @if(isset($item['author']) && filled($item['author']))
                    <author>{{ $item['author'] }}</author>
                @endif
                @if(isset($item['categories']) && is_array($item['categories']))
                    @foreach($item['categories'] as $category)
                        <category>{{ $category }}</category>
                    @endforeach
                @endif
                @if(isset($item['thumbnail']) && filled($item['thumbnail']))
                    <media:thumbnail>{{ $item['thumbnail'] }}</media:thumbnail>
                @endif
                @if(isset($item['media']) && is_array($item['media']))
                    @foreach($item['media'] as $media)
                        <media:content url="{{ $media['url'] }}" type="{{ $media['type'] }}"/>
                    @endforeach
                @endif
            </item>
        @endforeach
    </channel>
</rss>
