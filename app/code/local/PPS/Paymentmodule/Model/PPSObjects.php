<?php class AuthorizeByRoleOnOrder{public$OrderRepository;}class AuthorizeByRoleOnPayment{public$PaymentRepository;}class AuthorizeByRoleOnAutoClose{public$AutoCloseRepository;}class AuthorizeByRoleOnAccount{public$AccountRepository;}class AuthorizeByRoleOnCampaign{public$CampaingRepository;}class AuthorizeByRoleOnCustomer{public$MerchantRepository;}class ApplicationController{public$ApplicationRepository;public$AuthRepository;}class ComplianceSummaryController{public$ComplianceSummaryRepository;public$Logger;}class CustomerBankAccountController{public$CustomerRepository;public$Logger;}class CustomerCardAccountController{public$CustomerRepository;public$Logger;}class CustomerLoyaltyAccountController{public$CustomerRepository;public$Logger;}class CustomerPaymentController{public$CustomerRepository;public$PaymentRepository;public$Logger;}class CustomerPhotoController{public$CustomerRepository;public$Logger;}class CustomerContactController{public$CustomerRepository;public$Logger;}class EventController{public$Logger;public$repository;}class ExchangeController{public$PaymentRepository;public$Logger;}class PaymentContactController{public$PaymentRepository;public$Logger;}class PaymentSignatureController{public$PaymentRepository;public$Logger;}class InventoryTrackingController{public$InventoryTrackingRepository;public$Logger;}class ImportActionController{public$ImportRepository;public$Logger;public$ProductRepository;}class GeocodeActionController{public$GeocodeRepository;public$Logger;}class MerchantBillingPlanController{public$MerchantRepository;public$MerchantBillingPlanRepository;}class PlanActionController{public$Logger;}class ProductDiscountController{public$ProductRepository;public$Logger;}class ProductImageController{public$ProductRepository;public$Logger;}class SubscriptionController{public$Logger;public$repository;}class VariantController{public$ProductRepository;public$Logger;}class VariantLocationController{public$ProductRepository;public$Logger;}class ProductVariantPropertiesController{public$ProductRepository;public$Logger;}class Application{public$applicationId;public$name;public$description;public$apiKey;public$apiSecret;public$isDeleted;public$locked;public$merchantId;public$roleId;public$utcDateCreated;public$utcDateModified;public$utcDateDeleted;}class BankAccount{public$created;public$id;public$hash;public$last4;public$alias;public$token;public$routingNumber;public$accountNumber;public$contact;}class CardAccount{public$id;public$created;public$alias;public$hash;public$cardType;public$currency;public$entryMode;public$last4;public$token;public$number;public$expiryMonth;public$expiryYear;public$magstripe;public$cvv;public$name;public$avsStreet;public$avsZip;public$supportNumber;public$contact;}class ImportProduct{public$Department;public$Class;public$SubClass;public$Brand;public$Name;public$Manufacturer;public$ProductNumber;public$Vendor;public$Style;public$Description;public$PrimaryId;public$SecondaryId;public$Cost;public$Price;public$ReorderThreshold;public$Tags;public$CsvExtensions;}class ComplianceSummary{public$merchantId;}class Consumer{public$id;public$name;public$email;}class OrderReceiptController{public$OrderRepository;public$Logger;}class CardAccountTokenController{public$PaymentRepository;public$Logger;}class OrderPurchaseController{public$OrderRepository;public$Logger;}class OrderHistoryController{public$OrderRepository;public$Logger;}class OrderActionController{public$OrderRepository;public$Logger;}class CustomerAddressController{public$CustomerRepository;public$Logger;}class CustomerTagController{public$CustomerRepository;public$Logger;}class ProductLocationController{public$ProductRepository;public$Logger;}class VariantPhotoController{public$ProductRepository;public$Logger;}class ProductTaxController{public$ProductRepository;public$Logger;}class TaxCategoryTaxController{public$TaxRepository;public$Logger;}class CustomerSubscriptionController{public$CustomerRepository;public$Logger;}class DiscountTagController{public$DiscountRepository;public$Logger;}class OrderDiscountController{public$OrderRepository;public$Logger;}class OrderPaymentController{public$PaymentRepository;public$Logger;}class AccountActionController{public$AccountRepository;public$Logger;}class CampaignActionController{public$CampaignRepository;public$Logger;}class NotificationOptionActionController{public$NotificationRepository;public$Logger;}class ProductVariantController{public$ProductRepository;public$Logger;}class NotificationCategoryController{public$NotificationRepository;}class CustomerOrderController{public$CustomerRepository;public$Logger;}class LocationController{public$LocationRepository;public$Logger;}class LocationProductController{public$LocationRepository;public$Logger;}class PaymentController{public$PaymentRepository;public$Logger;}class HelpController{public$CurrentAssembly;}class PlanSubscriptionController{public$PlanRepository;public$Logger;}class ProductActionController{public$ProductRepository;public$Logger;}class ProductController{public$ProductRepository;public$Logger;}class ProductTagController{public$ProductRepository;public$Logger;}class SAQActionController{public$QuestionnaireRepository;public$Logger;}class TaxCategoryController{public$TaxRepository;public$Logger;}class VariantTagController{public$ProductRepository;public$Logger;}class ACHReport{public$merchantId;public$dba;public$applicationId;public$xmid;public$cubeDate;public$workOfDate;public$transmissionDate;public$achCodeDescription;public$achType;public$reserveFlag;public$routingNumberLastDigits;public$accountNumberLastDigits;public$creditCount;public$creditAmount;public$averageCreditAmount;public$averageMerchantCreditAmount;public$debitCount;public$debitAmount;public$averageDebitAmount;public$averageMerchantDebitAmount;public$netCount;public$netAmount;public$averageNetAmount;public$averageMerchantNetAmount;}class BatchReport{public$id;public$paymentType;public$batchNumber;public$dba;public$merchantId;public$xmid;public$deviceNumber;public$status;public$saleCount;public$saleAmount;public$returnCount;public$returnAmount;public$cashAdvanceCount;public$cashAdvanceAmount;public$prepaidCount;public$prepaidAmount;public$netCount;public$netAmount;public$opened;public$closed;public$userClosedBy;public$responseCode;public$responseMessage;public$geolocationOpened;public$geolocationClosed;public$deviceOpened;public$deviceClosed;public$merchant;public$device;public$recordCount;}class BillingImportSet{public$id;public$importNumber;public$description;public$merchantId;public$processStatus;public$customerCount;public$totalAmount;public$processDate;public$importDate;}class CardTypeRange{public$id;public$type;public$name;public$description;public$alphaNumericTwo;public$binStart;public$binEnd;public$length;public$isLuhnTen;}class ChargebacksReport{public$merchantId;public$dba;public$applicationId;public$xmid;public$cubeDate;public$workOfDate;public$transactionDate;public$amount;public$count;public$reasonCode;public$reasonCodeDescription;public$cardLastDigits;}class Contact{public$id;public$contactType;public$name;public$address;public$phone;public$mobile;public$email;public$communicationType;}class Coordinates{public$latitude;public$longitude;public$accuracy;public$altitude;public$altitudeAccuracy;public$heading;public$speed;}class CustomerMatch{public$created;public$modified;public$email;public$fax;public$phone;public$address;public$website;public$loyaltyStatus;public$loyaltyEnrolled;}class Event{public$id;public$merchantId;public$eventDate;public$EventType;}class EventSubscription{public$id;public$eventType;public$merchantId;public$created;public$modified;public$callbackUrl;}class Exchange{public$id;public$source;public$rate;public$margin;public$expiration;public$currency;public$baseCurrency;public$amount;public$baseAmount;public$created;}class ImportProductQuantity{public$Style;public$PrimaryId;public$SecondaryId;public$Name;public$OnHand;}class LoyaltyAccount{public$id;public$created;public$token;public$alias;}class Signature{public$id;public$Data;public$Created;}class InventoryTracking{public$id;public$importSetId;public$actionType;public$note;public$status;public$createdBy;public$createdDate;public$locationName;public$invoiceNumber;}class InventoryTrackingVariant{public$id;public$trackingId;public$itemId;public$variantId;public$salePrice;public$quantity;public$costBasis;public$orderId;public$note;public$isDeleted;public$utcDateDeleted;public$name;public$quantityReceived;}class ProductVariant{public$id;public$productId;public$dateCreated;public$userIdCreatedBy;public$variantName;public$price;public$cost;public$secondaryId;public$primaryId;public$code;public$condition;public$thumbnail;public$tags;public$size;public$costBasis;public$daysOnHandThreshold;public$inStock;public$onOrder;public$inventoryThreshold;public$lowInventoryNotify;public$hideIfNoStock;public$photos;public$quantity;}class MessageGroup{public$groupId;public$groupName;public$categories;}class ItemPairPercentage{public$firstItemId;public$secondItemId;public$firstItemCount;public$secondItemCount;public$pairCount;public$orderCount;public$firstItemName;public$secondItemName;public$pairPercentage;}class Loyalty{public$id;public$strategyType;public$initialValue;public$threshold;public$effectiveDate;public$redemptionType;public$discountId;public$pointValue;public$pointAccrualMethod;public$pointsPerValue;public$isActive;public$sendEmail;public$sendSMS;public$dateCreated;public$dateUpdated;public$merchants;}class LocationItem{public$rowNumber;public$id;public$setId;public$locationId;public$locationName;public$itemId;public$itemName;public$quantity;public$quantityOnHand;public$daysOnHand;public$valueOnHand;public$quantitySold;public$costBasis;public$created;public$createBy;public$actionType;}class MerchantBillingPlan{public$merchantId;public$xmid;public$name;public$billingPlan;public$isTrackingEnabled;}class MerchantRestriction{public$id;public$merchantId;public$restrictionTypeId;public$limit;}class OrderHistory{public$type;public$message;public$created;public$createdBy;public$userId;}class OrderSummary{public$id;public$merchantId;public$type;public$receiptNumber;public$invoiceNumber;public$customerName;public$customerNumber;public$isTaxExempt;public$returnReason;public$memo;public$isVoided;public$isVoidable;public$quantity;public$totalAmount;public$taxAmount;public$subTotalAmount;public$tipAmount;public$shipAmount;public$discountAmount;public$balance;public$dueDate;public$created;public$purchaseOrderNumber;public$invoiceDate;public$isClick2PayEnabled;public$sourceType;public$paymentType;}class ProductSoldReport{public$rowNumber;public$recordCount;public$pageCount;public$totalQuantity;public$totalCostAmount;public$totalSaleAmount;public$id;public$setId;public$itemNumber;public$name;public$description;public$brand;public$itemStyle;public$size;public$note;}class PaymentCheck{public$name;public$accountNumber;public$routingNumber;public$checkNumber;public$phoneNumber;}class Location{public$id;public$name;public$merchantId;public$created;public$modified;public$isDefault;public$address;}class Product{public$id;public$setId;public$number;public$productName;public$description;public$brand;public$style;public$note;public$modified;public$active;public$code;public$displayName;public$displayDescription;public$displayImage;public$vendor;public$manufacturer;public$briefDescription;public$category;public$primaryClass;public$subClass;public$taxable;public$height;public$width;public$depth;public$grams;public$volume;public$length;public$ISBN;public$hasPromotion;public$tags;public$productVariants;public$variantProperties;public$priceHistory;public$locations;public$taxCategory;public$department;public$inStock;}class ReceiptOptions{public$merchantId;public$imageWidth;public$imageHeight;public$imageSize;public$imageDataURLValue;public$imageName;public$created;public$modified;public$locationTitle;public$locationName;public$locationPhone;public$receiptMessage;public$storeEmail;public$websiteURL;public$legalURL;public$showHistory;public$bccReceiptTo;public$showLoyaltyPoints;public$address;}class Order{public$id;public$merchantId;public$type;public$receiptNumber;public$accessCode;public$invoiceNumber;public$isTaxExempt;public$returnReason;public$memo;public$isVoided;public$isVoidable;public$quantity;public$totalAmount;public$taxAmount;public$subTotalAmount;public$tipAmount;public$shipAmount;public$discountAmount;public$balance;public$dueDate;public$created;public$purchaseOrderNumber;public$invoiceDate;public$isClick2PayEnabled;public$sourceType;public$paymentType;public$purchases;public$discounts;public$customer;public$billingAddress;public$shippingAddress;public$meta;}class Registration{public$verification;public$answers;public$accountNumber;public$routingNumber;}class RegistrationQuestions{public$questions;}class DailyVolumeReport{public$merchantId;public$dba;public$applicationId;public$xmid;public$merchants;public$cubeDate;public$workOfDate;public$transactionDate;public$bankCardTransactions;public$bankCardAmount;public$averageBankCardAmount;public$nonBankCardTransactions;public$nonBankCardAmount;public$averageNonBankCardAmount;public$pinDebitTransactions;public$pinDebitAmount;public$averagePINDebitAmount;public$transactions;public$totalAmount;public$averageTotalAmount;public$averageMerchantTotalAmount;public$deviceId;public$device;public$batchNumberId;public$batchId;public$batchNumber;public$paymentType;public$devices;public$batches;public$cardTypes;}class DailyVolumeByPaymentTypeReport{public$cubeDate;public$cardTotal;public$checkTotal;public$cashTotal;}class DailyVolumePerformanceReport{public$merchantId;public$dba;public$applicationId;public$xmid;public$previousAmount;public$currentAmount;public$previousCount;public$currentCount;public$amountPercentageChange;public$countPercentageChange;}class MerchantCriticalSnapshotReport{public$merchantId;public$applicationId;public$dba;public$xmid;public$type;public$snapshotDate;public$amount;}class LedgerACHReport{public$workOfDate;public$amount;}class LedgerSummaryReport{public$category;public$yesterdayCount;public$yesterdayAmount;public$mtdCount;public$mtdAmount;public$ytdCount;public$ytdAmount;}class MerchantStatementPeriodReport{public$statementDate;}class MerchantStatementReport{public$merchantId;public$date;public$statement;public$byteData;}class RegistrationVerification{public$merchantId;public$xmid;public$name;public$city;public$state;public$zip;public$statementMonth;public$statementYear;public$dateApproved;public$questions;}class MerchantVolumeReport{public$merchantId;public$dba;public$applicationId;public$xmid;public$volume;}class NegativeTransactionReport{public$merchantId;public$dba;public$applicationId;public$xmid;public$dateType;public$cubeDate;public$transactionType;public$amount;}class LastAggregationDates{public$settlementLastUpdated;public$achLastUpdated;public$ChargebacksLastUpdated;}class InterchangeSales{public$recordCount;public$processorCountryCodeAlphabeticTwo;public$ticketFeeAttribute;public$ticketFeeDescription;public$cardTypeId;public$cardName;public$salesCount;public$salesCountPercent;public$salesAmount;public$salesAmountPercent;}class InterchangeSalesReport{public$salesCountTotal;public$salesCountPercentTotal;public$salesAmountTotal;public$salesAmountPercentTotal;public$Sales;}class Role{public$roleId;public$roleType;public$roleName;public$web;public$mobile;}class TaxCategory{public$id;public$name;public$code;public$description;public$created;public$modified;}class TimeProfile{public$id;public$name;public$merchantId;public$daysOfWeek;public$daysOfWeekString;public$startTimeMinuteOffset;public$startTimeWithOffset;public$endTimeMinuteOffset;public$endTimeWithOffset;public$timeZone;public$utcDateCreated;}class TopInventoryItemReport{public$rowNumber;public$recordCount;public$totalQuantitySold;public$totalAmountSold;public$totalQuantityReturned;public$totalAmountReturned;public$totalQuantityNet;public$totalAmountNet;public$id;public$setId;public$itemNumber;public$name;public$description;public$brand;public$itemStyle;public$size;public$isDeleted;public$note;}class TaxSummaryReport{public$recordCount;public$taxId;public$name;public$description;public$rate;public$orderCount;public$itemCount;public$totalTaxes;}class ProductSalesReport{public$recordCount;public$merchantId;public$dba;public$inventoryItemId;public$inventoryName;public$totalAmount;public$totalQuantity;public$totalTaxes;}class UserRegistration{public$userName;public$password;public$email;public$mobileNumber;public$firstName;public$lastName;public$authenticateSMS;public$authenticatePhone;}class UserReport{public$recordCount;public$userId;public$firstName;public$lastName;public$username;public$totalTipAmount;public$totalAmount;public$totalCount;}class CustomerSummaryReport{public$isNewCustomer;public$isRepeatCustomer;public$isOneTimeCustomer;public$customerCount;public$orderCount;public$totalAmount;}class DeviceReport{public$recordCount;public$deviceId;public$deviceName;public$totalAmount;public$totalCount;}class MerchantInfo{public$id;public$dba;public$xmid;public$processor;public$merchantId;public$customerServicePhone;public$tin;public$address;public$merchantCategoryCode;public$isEcomMerchant;public$isMotoMerchant;public$userId;public$udid;public$userName;public$signatureKey;public$processor_TerminalId;public$processor_MerchantId;public$processor_SecurityCode;}class MerchantTransactionReport{public$merchantId;public$dba;public$xmid;public$amount;public$count;}class DetailedTransactionReport{public$transactionId;public$transactionDate;public$originalId;public$isOrderRequired;public$isApproved;public$hasSignature;public$isSettled;public$batchId;public$status;public$deviceNumber;public$batchNumber;public$xmidD;public$sequenceNumber;public$revisionNumber;public$transactionType;public$paymentType;public$isAdjusted;public$isReversed;public$isVoided;public$cardName;public$cardTypeId;public$entryType;public$pan4;public$pan2;public$checkNumber;public$checkFirstName;public$checkLastName;public$checkName;public$checkPhoneNumber;public$expirationDate;public$cvvIndicator;public$cardVerificationValue;public$surchargeAmount;public$cashbackAmount;public$tipAmount;public$taxAmount;public$totalAmount;public$balance;public$tenderedAmount;public$originalAmount;public$authorizedAmount;public$invoiceNumber;public$referenceNumber;public$authorizationCode;public$bankNetReferenceNumber;public$requestACI;public$responseCode;public$responseCodeEx;public$responseMessage;public$cardVerificationValueRespons;public$remainingBalanceMessage;public$remainingBalance;public$approvedAmountMessage;public$approvedAmount;public$requestedAmountMessage;public$requestedAmount;public$responseACI;public$avsAddress;public$avsPostalCode;public$avsResponse;public$motoEcomFlag;public$operatorId;public$isRecurringFlag;public$validationCode;public$bankNetReferenceDate;public$merchantId;public$referenceNumberDCC;public$allowPartialReversals;public$token;public$primaryId;public$customer;public$productVariant;}class CustomerLoyalty{public$id;public$loyaltyId;public$customerId;public$value;public$lifetimeValue;public$redemptionValue;public$modified;public$created;public$strategyType;}class Device{public$id;public$name;public$description;public$deviceType;public$deviceTypeName;public$UDID;public$enabled;public$onSuccessUrl;public$onFailureUrl;public$merchantId;public$dba;}class Discount{public$id;public$merchantId;public$dba;public$discountType;public$discountRateType;public$discountApplicationType;public$name;public$description;public$rate;public$accessCode;public$code;public$startDate;public$endDate;public$isExclusive;public$isApprovalRequired;public$created;public$modified;public$tags;}class FraudSetting{public$id;public$merchantId;public$keyedAVSAddress;public$keyedAVSPostalCode;public$keyedCVV;public$swipedAVSAddress;public$swipedAVSPostalCode;public$swipedLastFourDigits;public$dba;public$xmid;}class AuthToken{public$oauth_token;public$oauth_token_secret;public$oauth_callback_confirmed;}class Geocode{public$id;public$name;public$merchantId;public$latitude;public$longitude;public$radius;public$displayUnitOfMeasure;public$created;public$accuracy;public$altitude;public$altitudeAccuracy;public$heading;public$speed;public$address;public$dba;}class ImportResult{public$importSetId;public$messages;public$isValid;}class InventorySet{public$id;public$name;public$isTrackingEnabled;public$daysOnHandThresholdDefault;public$velocityWindowDaysDefault;public$created;public$modified;public$items;}class IPAddress{public$id;public$merchantId;public$name;public$ipaddress;public$dba;}class ItemMerchantDetail{public$id;public$merchantId;public$itemId;public$count;public$price;public$priceHistory;public$taxes;public$discounts;}class ItemPriceHistory{public$id;public$startDate;public$endDate;public$originalPrice;public$newPrice;}class LocationDistribution{public$merchantId;public$locationType;public$locationName;public$count;public$percent;}class MerchantCategory{public$id;public$parentId;public$name;public$children;public$mappedMerchants;}class MerchantClassification{public$cardTypeId;public$cardName;public$transactionCount;public$internetMerchant;public$saq;public$netScan;public$onsiteReview;public$level;}class MerchantSAQ{public$id;public$merchantId;public$saqType;public$saqPassed;public$saqStatus;public$created;public$completed;public$expires;public$signedBy;}class Message{public$id;public$messageCategoryId;public$userId;public$merchantId;public$from;public$subject;public$body;public$priority;public$priorityType;public$isRead;public$created;public$dateExpires;}class MessageCategory{public$id;public$messageCategoryName;}class PCIClassification{public$id;public$paymentBrandId;public$internetMerchant;public$level;public$annualCountLow;public$annualCountHigh;public$priorBreach;public$brandDiscretion;public$brandParity;public$onsiteReview;public$saq;public$netScan;public$complianceValidation;}class Photo{public$id;public$variantId;public$name;public$data;public$size;public$format;public$height;public$width;}class RegistrationQuestion{public$text;public$options;public$question;}class SelfAssessmentAnswer{public$id;public$answer;public$reason;}class SettlementTransactionReport{public$floatTicketNumber;public$floatTicketDays;public$cardholderTypeOrHeldSwitch;public$etcEntryMode;public$tnterchangeAssociationFlag;public$bjReasonCodeOrMITSUType;public$floorLimitFlag;public$etcTransactionType;public$etcTransactionTime;public$promotionId;public$firstDataTransactionId;public$fdrTransactionSourceId;public$fdrTransactionId;public$discountMethodCode;public$isDiscountRounded;public$baseDiscountAmount;public$promotionDiscountAmount;public$baseDiscountRate;public$discountBreakoutCode;public$customerName;public$promotionDiscountCalculationCode;public$promotionDiscountMethodId;public$merchantGrid;public$trustFundOneAmount;public$trustFundTwoAmount;public$additionalDiscountRate;public$additionalDiscountAmount;public$additionalPerItemDiscountAmount;public$breakPointAmount;public$aboveBelowRateCode;public$cardTypeExternalId;public$fundingTimingCode;public$fundingFrequencyCode;public$isFundingAccrued;public$headquartersDailyACHCode;public$retailAdjustmentDiscountCode;public$merchantHybridCode;public$transactionId;public$batchId;public$batchNumber;public$batchTerminalId;public$fileId;public$enteredReferenceNumber;public$sequenceNumber;public$gateway;public$gatewayInvoice;public$gatewayDeviceId;public$gatewaySettlementDate;public$gatewaySourceJobId;public$gatewaySourceJobName;public$gatewayRecordCreateJobId;public$gatewayRecordCreateJobName;public$transactionTypeId;public$transactionTypeName;public$transactionTypeSubName;public$transactionTypeDescription;public$cardholderSystemBankNumberId;public$cardholderSystemBankNumber;public$cardholderAccountNumberHash;public$cardholderAccountNumberLastDigits;public$posted;public$transactionAmount;public$transactionDate;public$ticketFeeAttribute;public$ticketFeeDescription;public$issuerFee;public$acquirerFee;public$baselineFee;public$reclassificationCode;public$reclassificationName;public$visaMerchantVolumeId;public$processorCountryCodeId;public$processorCountryCodeAlphabeticTwo;public$processorCountryCodeAlphabeticThree;public$processorCountryCodeNumeric;public$processorCountryName;public$cycleId;public$platformCode;public$platformName;public$cashbackAmount;public$isReimbursement;public$paymentType;public$cardTypeId;public$cardName;public$fundingTypeId;public$merchantLabeledCardTypeId;public$merchantLabeledCardName;public$cardProductCode;public$cardProductDescription;public$routingDestinationCardTypeId;public$routingDestinationCardName;public$isDebitCard;public$posEntryModeId;public$posEntryMode;public$posEntryModeDescription;public$isKeyEntered;public$isDebitTransaction;public$isAuthorized;public$retailInvoiceNumber;public$oilInvoiceNumber;public$cardNumber;public$retailDiscountRate;public$visaTransactionId;public$referenceNumber;public$authorizationCode;public$clientDefinedReferenceNumber;public$merchantTypeCode;public$batchDate;public$retailTerminalCode;public$disbursementMethodCode;public$initialTransactionAmount;public$additionalTransactionId;public$captureCodeId;public$captureCode;public$captureCodeDescription;public$oilTradeClassCode;public$invoiceId;public$merchantSICategoryId;public$merchantSICategory;public$merchantSICategoryDescription;public$transactionFeeAttributeCode;public$transactionFeeDescription;public$onlineDebitFeeAmount;public$ctPrtcCode;public$acquirerInternationalServiceAssessmentAmount;public$acquirerInternationalServiceAssessmentRate;public$networkAccessBrandUsageFeeAmount;public$acquirerSupportFeeAmount;public$acquirerSupportFeeRate;public$crossBorderFeeAmount;public$crossBorderFeeRate;public$acquirerInternationalFeeAmount;public$acquirerInternationalFeeRate;public$created;}class Subscription{public$id;public$startDate;public$endDate;public$lastRunDate;public$endAfterOccurences;public$completeOccurences;public$invoiceMethod;public$cardAccountId;public$paymentInitiationCode;public$allowPartialPayment;public$status;public$remindBeforeDueDate;public$remindAfterDueDate;public$reminderContact;public$invoiceContact;public$receiptContact;public$created;public$modified;public$customerId;}class OrderController{public$OrderRepository;public$PaymentRepository;public$CustomerRepository;public$Logger;}class Account{public$id;public$userName;public$email;public$firstName;public$lastName;public$password;public$mobileNumber;public$requireTxtAuthentication;public$txtAuthenticationPhone;public$locked;public$lastLogin;public$created;public$merchants;public$gadgets;}class Address{public$id;public$description;public$address1;public$address2;public$city;public$state;public$zip;public$country;public$created;}class AutoClose{public$id;public$name;public$days;public$time;public$offset;public$created;public$lastExecuted;}class Campaign{public$id;public$campaignName;public$description;public$merchantId;public$dba;public$type;public$via;public$headerMessage;public$newsletterMessage;public$launched;public$contacted;public$activity;public$created;}class CampaignTarget{public$id;public$purchaseBrand;public$purchaseName;public$purchaseTag;public$startPurchaseDate;public$endPurchaseDate;public$startPurchaseAmount;public$endPurchaseAmount;}class Plan{public$id;public$merchantId;public$name;public$memo;public$terms;public$interval;public$frequency;public$weekDay;public$month;public$dayNumber;public$dayType;public$day;public$purchases;public$discounts;public$totalAmount;public$taxAmount;public$subTotalAmount;public$discountAmount;public$subscriptionCount;public$lastInvoiceSentDate;}class Purchase{public$id;public$productId;public$productName;public$description;public$brand;public$style;public$size;public$quantity;public$price;public$discountAmount;public$subTotalAmount;public$taxRate;public$taxAmount;public$totalAmount;public$trackingNumber;public$created;public$productVariant;public$taxes;public$discounts;}class Tax{public$id;public$name;public$description;public$rate;public$created;public$modified;public$taxCategories;}class TransactionLimit{public$id;public$merchantId;public$saleLimit;public$refundLimit;}class Customer{public$id;public$created;public$name;public$website;public$number;public$taxId;public$note;public$email;public$mobile;public$phone;public$fax;public$visits;public$spend;public$lastVisit;public$lastSpend;public$lastPaymentMethod;public$receiveEmailPromotions;public$receiveMobilePromotions;public$loyaltyStatus;public$points;public$lifetimePoints;public$redeemableAmount;public$loyaltyEnrolled;}class Payment{public$created;public$id;public$creatorName;public$creatorAppName;public$replayId;public$merchantId;public$deviceId;public$tenderType;public$currency;public$amount;public$tax;public$tip;public$tags;public$meta;public$cardAccount;public$bankAccount;public$loyaltyAccount;public$authOnly;public$authCode;public$status;public$fraudScore;public$fraudScoreMeta;public$requireSignature;public$customer;public$customerMatches;public$settledAmount;public$settledCurrency;public$exchangeRate;public$estimatedDepositDate;public$cardPresent;}class AuthController{public$AuthRepository;public$AccountRepository;public$Logger;}class CardTypeController{public$CardTypeRepository;public$AuthRepository;public$Logger;}class AutoCloseController{public$AutoCloseRepository;public$Logger;}class CampaignController{public$CampaignRepository;public$Logger;}class CaptchaController{public$CaptchaRepository;public$Logger;}class NotificationController{public$NotificationRepository;public$Logger;}class AccountController{public$AccountRepository;public$Logger;}class CustomerController{public$CustomerRepository;public$Logger;}class LoyaltyController{public$LoyaltyRepository;public$Logger;}class InvoiceImportController{public$InvoiceImportRepository;public$Logger;}class PlanController{public$PlanRepository;public$Logger;}class ReportController{public$ReportRepository;public$Logger;}class ReceiptOptionController{public$ReceiptOptionsRepository;public$Logger;}class MerchantCategoryController{public$MerchantCategoryRepository;public$Logger;}class MerchantController{public$MerchantRepository;public$Logger;}class RegistrationActionController{public$RegistrationRepository;public$Logger;}class SAQController{public$QuestionnaireRepository;public$Logger;}class PCIController{public$PCIClassificationRepository;public$Logger;}class MerchantClassificationController{public$MerchantClassificationRepository;public$Logger;}class DeviceController{public$DeviceRepository;public$Logger;}class TimeProfileController{public$TimeProfileRepository;public$Logger;}class IPAddressController{public$IPAddressRepository;public$Logger;}class GeocodeController{public$GeocodeRepository;public$Logger;}class DiscountController{public$DiscountRepository;public$MerchantRepository;public$Logger;}class TagController{public$TagRepository;public$Logger;}class TaxController{public$TaxRepository;public$Logger;}class FraudSettingController{public$FraudSettingRepository;public$Logger;}class Merchant{public$id;public$merchantId;public$merchantDBA;public$xmid;public$roleId;public$role;public$deviceRestrictions;public$geocodeRestrictions;public$timeRestrictions;public$ipAddressRestrictions;public$transactionRestrictions;public$totalAmount;}class TransactionReport{public$transactionId;public$workOfDate;public$transactionDate;public$status;public$paymentType;public$cardTypeId;public$cardName;public$transactionTypeId;public$transactionType;public$transactionTypeName;public$deviceId;public$device;public$batchNumberId;public$batchId;public$batchNumber;public$transactionAmount;public$authorizationCode;public$cardLastDigits;public$isVoided;}class UserMessageDeliveryPreference{public$id;public$userId;public$groupId;public$messageCategoryId;public$messageCategoryName;public$sendEmail;public$sendSMS;public$limit;}class VariantProperties{public$id;public$property;}class PaymentCustomerController{public$CustomerRepository;public$PaymentRepository;public$Logger;}class PaymentReceiptController{public$OrderRepository;public$PaymentRepository;public$Logger;} ?>