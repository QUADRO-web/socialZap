# socialZap
socialZap extra for MODX CMS. Automaticaly Post social Media to your MODX website by the Service Zapier.

## Setup Zap to post media to your MODX Website
### Insgtagram
Use this template to setup a zap in Zapier: https://zapier.com/shared/fb926367f974e3de0d125cb415bd137bd53f63b0

Connect your INstagram account with the zap.

Zapier Webhook Settings:
| setting | default |
| --- | --- |
| URL | https://www.yourdomain.de/assets/components/socialzap/connector.php?action=importinstagram |
| Payload Type | json |

Leave every other field as default.

Using a secret Key for authorisation is optional. If you set a secret Key in your modx system settings add the secret to your webhook-url like this: https://www.yourdomain.de/assets/components/socialzap/connector.php?action=importinstagram&secret=yousecret


## Example MODX setup
socialZap has an internal caching. You can call it uncached.
```
[[!socialZap]]
```

## Properties
| setting | default | description |
| --- | --- | --- |
| &tpl | socialFeedTpl | Customize your layout with your indiviuell chunk. |
| &limit | 0 | Limit your media quantity. |
| &offset | 0 | Offset your media to use with pagination for example. |
| &sortby | date |  |
| &sortdir | desc | Option: desc/asc |
| &filterUser |  | Filter media by username. Usefull if you have different channels in your feed and you want to show different accounts in different sections of your site. |
| &filterContent |  | Filter media by string in content. For example: #youtag to filter media by hastags. |
| &filterSource |  | Filter media by source type. For example: "tiktok" to show only media from Tiktok. Or "instagram,tiktok" to show media from Tiktok and Instagram Channels. |
| &cache | true | Option: True/False to enable/disable caching. |
| &cacheTime | 3600 | Seconds to refresh Cache. You can actually set this up very high because your cron-job will clear the cache if there is new media available. |
| &cacheKey | socialZap | Cahing-Key |

## Placeholders: &tpl
| tag | description |
| --- | --- |
| id | ID |
| idx | Increasing Number |
| username | The authors username  |
| source | social media platform: instagram, tiktok ... |
| type | Media-Type: IMAGE / VIDEO / EMBED |
| image | URL to the image thumb |
| url | URL to the Media-File Image/Video |
| permalink | URL to the post/website |
| content | Content of the media |
| date | Date of Publishing the media |
| properties | properties can be called by prefixing them: +properties.yourname |

