ARG WWWUSER
ARG WWWGROUP

RUN groupadd -g $WWWGROUP sail && \
    useradd -u $WWWUSER -g sail -m sail

USER sail
