FROM ubuntu:22.04

ENV DEBIAN_FRONTEND noninteractive

# таблица с соответсвиями протобаф и grpc https://github.com/grpc/grpc/tree/master/src/php
ENV PROTOBUF_VERSION 3.20.1
ENV GRPC_VERSION 1.42.0

ENV PHP_VERSION 8.1
ENV GO_VERSION 1.17.7
ENV PB_REL "https://github.com/protocolbuffers/protobuf/releases"
ENV PROTONAME "protoc-${PROTOBUF_VERSION}-linux-x86_64"
ENV HOME /home
ENV GO111MODULE on
ENV GOPATH ${HOME}/go
ENV GOROOT /usr/local/go
ENV PATH $PATH:$GOROOT/bin:$GOPATH/bin

RUN apt-get update && apt-get install -y php-pear php${PHP_VERSION} php${PHP_VERSION}-dev phpunit autoconf automake libtool make gcc curl unzip zip libtool zlib1g-dev cmake
RUN mkdir /tmp/protoc && curl -OL "${PB_REL}/download/v${PROTOBUF_VERSION}/${PROTONAME}.zip" && unzip ${PROTONAME} -d /tmp/protoc && mv /tmp/protoc/bin/* /usr/local/bin/ && mv /tmp/protoc/include/* /usr/local/include/

RUN yes "" | pecl install protobuf-${PROTOBUF_VERSION}
RUN yes "" | pecl install grpc-${GRPC_VERSION}

RUN curl -OL https://dl.google.com/go/go${GO_VERSION}.linux-amd64.tar.gz && tar -C /tmp -xzf go${GO_VERSION}.linux-amd64.tar.gz && mv /tmp/go /usr/local
RUN go get github.com/golang/protobuf/protoc-gen-go && go get google.golang.org/grpc@v${GRPC_VERSION}

RUN apt-get install -y git
RUN cd /tmp && git clone -b v${GRPC_VERSION} https://github.com/grpc/grpc && cd grpc && git submodule update --init && mkdir -p cmake/build && cd cmake/build && cmake ../.. && make -j4 protoc grpc_php_plugin
