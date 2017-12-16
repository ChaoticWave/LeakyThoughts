## About LeakyThoughts
LeakyThoughts is a collection of command line tools that will allow you to manipulate and load mailbox files into an [Elastic stack](https://www.elastic.co/webinars/introduction-elk-stack) for analysis.

If you're in need of some data to analyze, there are tons of files here: https://github.com/datahoarder/secretary-clinton-email-dump

### Usage
This is a baseline Laravel application with two CLI commands added: Split and Load. The first splits a mailbox file into individual files. The second loads individual files into an Elastic stack. They can be used together or separate.

They are accessed by using the `artisan` command as follows:

```
$ php artisan leaky:split /path/to/file
```

and

```
$ php artisan leaky:load [path]
```

At this point it's very simplistic. It parses the files and mimics a Logstash for Kibana. If the date of a mail file can be determined it will be used as the timestamp for the record in Elasticsearch. When you go into Kibana, make sure you set your time range as large as possible.
 
More documentation will be forthcoming.

## Elastic Stack
Here are some examples on getting your Elastic Stack up and running: https://github.com/elastic/examples

## License
LeakyThoughts is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
