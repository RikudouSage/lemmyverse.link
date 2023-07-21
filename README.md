[![Coverage Status](https://img.shields.io/coverallsCoverage/github/RikudouSage/lemmyverse.link)](https://coveralls.io/github/RikudouSage/lemmyverse.link?branch=master)
[![Tests](https://github.com/RikudouSage/lemmyverse.link/actions/workflows/tests.yaml/badge.svg)](https://github.com/RikudouSage/lemmyverse.link/actions/workflows/tests.yaml)

# Lemmyverse.link

This is a redirect service for linking to Lemmy communities throughout the internet. When you're on Lemmy, universal
links work (either in the form of relative link or the `!community_name@instance.tld` form).

But when linking to a Lemmy community from outside Lemmy, you face the problem of forcing the user to go to the instance
you linked to instead of their own.

Using this project you can create a link like this: `https://lemmyverse.link/c/community_name@instance.tld`, the user
will be given the option to set their home instance and every further link to `lemmyverse.link` will work as usual.

![Preview of a screen for setting instance to redirect](doc/assets/lemmy-01.png)

![Preview of a screen with redirect to target instance](doc/assets/lemmy-02.png)

## Available domains

This project is currently hosted on:

- lemmyverse.link
- threadiverse.link

## Translating

If you'd like to translate this project to your language, run the following command:

`./bin/console translation:extract --force --format yaml [language]`

Replace `[language]` with your two-letter country code, for example for German it would be:

`./bin/console translation:extract --force --format yaml de`

Edit the file `translations/messages+intl-icu.[language].yaml`


## Deploying

If you want to deploy this project using serverless, follow these steps:

- `export DOMAIN_NAME=lemmyverse.link` (replace `lemmyverse.link` with your domain)
- `export AWS_REGION=eu-central-1`
- `rm -rf ./var/{cache,log} public/build`
- `APP_ENV=prod composer install --no-dev --no-scripts`
- `yarn install`
- `yarn build`
- `./bin/console cache:warmup --env=prod`
- `export DOMAIN_ZONE=XXX` (replace `XXX` with your AWS domain zone id)
- `export DOMAIN_ID=Lemmyverse` (replace `Lemmyverse` with any identifier for your domain)
- `serverless deploy --stage prod --verbose --region $AWS_REGION`
- `export ASSETS_BUCKET=$(aws cloudformation describe-stacks --stack-name LemmyverseLink-$DOMAIN_ID-prod --query "Stacks[0].Outputs[?OutputKey=='AssetsBucket'].OutputValue" --output=text --region $AWS_REGION)`
- `export CDN_ID=$(aws cloudformation describe-stacks --stack-name LemmyverseLink-$DOMAIN_ID-prod --query "Stacks[0].Outputs[?OutputKey=='Cdn'].OutputValue" --output=text --region $AWS_REGION)`
- `aws s3 sync public/build s3://$ASSETS_BUCKET/build --delete`
- `aws cloudfront create-invalidation --distribution-id $CDN_ID --paths "/*"`

### Removing deployed code

- `export DOMAIN_ID=Lemmyverse` (replace `Lemmyverse` with any identifier for your domain)
- `export AWS_REGION=eu-central-1`
- `export ASSETS_BUCKET=$(aws cloudformation describe-stacks --stack-name LemmyverseLink-$DOMAIN_ID --query "Stacks[0].Outputs[?OutputKey=='AssetsBucket'].OutputValue" --output=text --region $AWS_REGION)`
- `aws s3 rm s3://$ASSETS_BUCKET/ --recursive`
- `serverless remove --stage prod --verbose --region $AWS_REGION`

